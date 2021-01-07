<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

use Mollie\Config\Config;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Exception\OrderCreationException;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Handler\Exception\OrderExceptionHandler;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\ExceptionService;
use Mollie\Service\MemorizeCartService;
use Mollie\Service\PaymentMethodService;
use Mollie\Utility\PaymentFeeUtility;
use MolliePrefix\Mollie\Api\Resources\Order as MollieOrderAlias;
use MolliePrefix\Mollie\Api\Resources\Payment as MolliePaymentAlias;
use MolliePrefix\Mollie\Api\Resources\PaymentCollection;
use MolliePrefix\Mollie\Api\Types\PaymentMethod;
use MolliePrefix\Mollie\Api\Types\PaymentStatus;
use MolliePrefix\PrestaShop\Decimal\Number;

if (!defined('_PS_VERSION_')) {
	return;
}

require_once dirname(__FILE__) . '/../../mollie.php';

/**
 * Class MolliePaymentModuleFrontController.
 *
 * @property Context $context
 * @property Mollie  $module
 */
class MolliePaymentModuleFrontController extends ModuleFrontController
{
	/** @var bool */
	public $ssl = true;

	/** @var bool */
	public $display_column_left = false;

	/** @var bool */
	public $display_column_right = false;

	/**
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function initContent()
	{
		parent::initContent();
		/** @var Cart $cart */
		$cart = $this->context->cart;
		$customer = new Customer($cart->id_customer);
		$this->context->smarty->assign('link', $this->context->link);

		if (!$this->validate(
			$cart,
			$customer
		)) {
			/** @var Mollie\Service\LanguageService $langService */
			$langService = $this->module->getMollieContainer(Mollie\Service\LanguageService::class);
			$this->errors[] = $langService->getLang()['This payment method is not available.'];
			$this->setTemplate('error.tpl');

			return;
		}

		$method = Tools::getValue('method');
		if (in_array($method, [Config::CARTES_BANCAIRES])) {
			$method = 'creditcard';
		}
		$issuer = Tools::getValue('issuer') ?: null;

		$originalAmount = $cart->getOrderTotal(
			true,
			Cart::BOTH
		);
		$amount = $originalAmount;
		if (!$amount) {
			Tools::redirectLink('index.php');
		}

		/** @var PaymentMethodRepository $paymentMethodRepo */
		$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
		/** @var PaymentMethodService $paymentMethodService */
		$paymentMethodService = $this->module->getMollieContainer(PaymentMethodService::class);

		$environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
		$paymentMethodId = $paymentMethodRepo->getPaymentMethodIdByMethodId($method, $environment);
		$paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
		// Prepare payment
		do {
			$orderReference = Order::generateReference();
		} while (Order::getByReference($orderReference)->count());
		$paymentData = $paymentMethodService->getPaymentData(
			$amount,
			Tools::strtoupper($this->context->currency->iso_code),
			$method,
			$issuer,
			(int) $cart->id,
			$customer->secure_key,
			$paymentMethodObj,
			false,
			$orderReference,
			Tools::getValue('cardToken')
		);

		$apiPayment = $this->createMollieOrder($paymentData, $paymentMethodObj);
		if (!$apiPayment) {
			return;
		}

		$this->createOrder($method, $apiPayment, $cart->id, $originalAmount, $customer->secure_key, $orderReference);
		$orderReference = isset($apiPayment->metadata->order_reference) ? pSQL($apiPayment->metadata->order_reference) : '';

		$orderId = Order::getOrderByCartId($cart->id);
		$order = new Order($orderId);
		if (PaymentMethod::BANKTRANSFER !== $apiPayment->method) {
			try {
				Db::getInstance()->insert(
					'mollie_payments',
					[
						'cart_id' => (int) $cart->id,
						'order_id' => (int) $order->id,
						'method' => pSQL($apiPayment->method),
						'transaction_id' => pSQL($apiPayment->id),
						'order_reference' => pSQL($orderReference),
						'bank_status' => PaymentStatus::STATUS_OPEN,
						'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
					]
				);
			} catch (PrestaShopDatabaseException $e) {
				$paymentMethodRepo->tryAddOrderReferenceColumn();
				throw $e;
			}
		}

		// Go to payment url
		if (null !== $apiPayment->getCheckoutUrl()) {
			Tools::redirect($apiPayment->getCheckoutUrl());
		} else {
			Tools::redirect($apiPayment->redirectUrl);
		}
	}

	/**
	 * Checks if this payment option is still available
	 * May redirect the user to a more appropriate page.
	 *
	 * @param Cart $cart
	 * @param Customer $customer
	 *
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function validate($cart, $customer)
	{
		if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
			// We be like: how did you even get here?
			Tools::redirect(Context::getContext()->link->getPageLink('index', true));

			return false;
		}

		$authorized = false;

		foreach (Module::getPaymentModules() as $module) {
			if ($module['name'] === $this->module->name) {
				$authorized = true;
				break;
			}
		}

		if (!$authorized) {
			return false;
		}

		if (!Validate::isLoadedObject($customer)) {
			return false;
		}

		return true;
	}

	/**
     * @param PaymentData|OrderData $paymentData
     * @param MolPaymentMethod $paymentMethodObj
	 *
	 * @return false|MollieOrderAlias|MolliePaymentAlias
	 *
	 * @throws PrestaShopException
	 */
	protected function createMollieOrder($paymentData, $paymentMethodObj)
	{
		try {
			$apiPayment = $this->createPayment($paymentData->jsonSerialize(), $paymentMethodObj->method);
		} catch (Exception $e) {
			$paymentData->setDeliveryPhoneNumber(null);
			$paymentData->setBillingPhoneNumber(null);
			try {
				$apiPayment = $this->createPayment($paymentData->jsonSerialize(), $paymentMethodObj->method);
			} catch (OrderCreationException $e) {
				$errorHandler = ErrorHandler::getInstance();
				$errorHandler->handle($e, $e->getCode(), false);

				$this->setTemplate('error.tpl');

				if (Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)) {
					$message = 'Cart Dump: ' . $e->getMessage() . ' json: ' . json_encode($paymentData, JSON_PRETTY_PRINT);
				} else {
					/** @var ExceptionService $exceptionService */
					$exceptionService = $this->module->getMollieContainer(ExceptionService::class);
					$message = $exceptionService->getErrorMessageForException($e, $exceptionService->getErrorMessages());
				}
				$this->errors[] = $message;

				return false;
			} catch (PrestaShopException $e) {
				$errorHandler = ErrorHandler::getInstance();
				$errorHandler->handle($e, $e->getCode(), false);

				$this->setTemplate('error.tpl');
				$this->errors[] = Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)
					? $e->getMessage() . ' Cart Dump: ' . json_encode($paymentData, JSON_PRETTY_PRINT)
					: $this->module->l('An error occurred while initializing your payment. Please contact our customer support.', 'payment');

				return false;
			}
		}

		return $apiPayment;
	}

	/**
	 * @param array $data
	 * @param string $selectedApi
	 *
	 * @return MollieOrderAlias|MolliePaymentAlias
	 *
	 * @throws OrderCreationException
	 */
	protected function createPayment($data, $selectedApi)
	{
		try {
			if (Mollie\Config\Config::MOLLIE_ORDERS_API === $selectedApi) {
				/** @var MollieOrderAlias $payment */
				$payment = $this->module->api->orders->create($data, ['embed' => 'payments']);
			} else {
				/** @var MolliePaymentAlias $payment */
				$payment = $this->module->api->payments->create($data);
			}

			return $payment;
		} catch (Exception $e) {
			/** @var OrderExceptionHandler $orderExceptionHandler */
			$orderExceptionHandler = $this->module->getMollieContainer(OrderExceptionHandler::class);

			throw $orderExceptionHandler->handle($e);
		}
	}

	/**
	 * Prepend module path if PS version >= 1.7.
	 *
	 * @param string $template
	 * @param array $params
	 * @param string|null $locale
	 *
	 * @throws PrestaShopException
	 *
	 * @since 3.3.2
	 */
	public function setTemplate($template, $params = [], $locale = null)
	{
		if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			$template = "module:mollie/views/templates/front/17_{$template}";
		}

		/* @phpstan-ignore-next-line */
		parent::setTemplate($template, $params, $locale);
	}

	private function createOrder($method, $apiPayment, $cartId, $originalAmount, $secureKey, $orderReference)
	{
		$extraVars = [];
		if (PaymentMethod::BANKTRANSFER === $method) {
			try {
				Db::getInstance()->insert(
					'mollie_payments',
					[
						'cart_id' => (int) $cartId,
						'method' => pSQL($apiPayment->method),
						'transaction_id' => pSQL($apiPayment->id),
						'order_reference' => pSQL($orderReference),
						'bank_status' => PaymentStatus::STATUS_OPEN,
						'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
					]
				);
			} catch (PrestaShopDatabaseException $e) {
				/** @var PaymentMethodRepository $paymentMethodRepo */
				$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
				$paymentMethodRepo->tryAddOrderReferenceColumn();
				throw $e;
			}

			// Set the `banktransfer` details
			if ($apiPayment instanceof MollieOrderAlias) {
				// If this is an order, take the first payment
				/** @var PaymentCollection $payments */
				$payments = $apiPayment->payments();
				$apiPayment = $payments[0];
			}

			$details = $apiPayment->details->transferReference;
			$address = "IBAN: {$apiPayment->details->bankAccount} / BIC: {$apiPayment->details->bankBic}";

			$extraVars = [
				'{bankwire_owner}' => 'Stichting Mollie Payments',
				'{bankwire_details}' => $details,
				'{bankwire_address}' => $address,
			];
		}
		/** @var PaymentMethodRepository $paymentMethodRepo */
		$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
		$environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);

		$orderFee = PaymentFeeUtility::getPaymentFee(
			new MolPaymentMethod(
				$paymentMethodRepo->getPaymentMethodIdByMethodId($apiPayment->method, $environment)
			),
			$this->context->cart->getOrderTotal()
		);

		$totalPrice = new Number((string) $originalAmount);

		$orderFeeNumber = new Number((string) 0);
		if ($orderFee) {
			$orderFeeObj = new MolOrderFee();
			$orderFeeObj->id_cart = (int) $cartId;
			$environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
			$orderFeeObj->order_fee = PaymentFeeUtility::getPaymentFee(
				new MolPaymentMethod(
					$paymentMethodRepo->getPaymentMethodIdByMethodId($apiPayment->method, $environment)
				),
				$this->context->cart->getOrderTotal()
			);
			try {
				$orderFeeObj->add();
			} catch (Exception $e) {
				$errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
				$errorHandler->handle($e, $e->getCode(), false);
				throw new PrestaShopException('Can\'t save Order fee');
			}
			$orderFeeNumber = new Number((string) $orderFeeObj->order_fee);
			$totalPrice = $orderFeeNumber->plus($totalPrice);
		}

		$this->module->validateOrder(
			(int) $cartId,
			(int) Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_AWAITING),
			(float) $totalPrice->toPrecision(2),
			isset(Mollie\Config\Config::$methods[$apiPayment->method]) ? Mollie\Config\Config::$methods[$method] : $this->module->name,
			null,
			$extraVars,
			null,
			false,
			$secureKey
		);

		$orderid = Order::getOrderByCartId($cartId);
		$order = new Order($orderid);
		$order->total_paid_tax_excl = (float) $orderFeeNumber->plus(new Number((string) $order->total_paid_tax_excl))->toPrecision(2);
		$order->total_paid_tax_incl = (float) $orderFeeNumber->plus(new Number((string) $order->total_paid_tax_incl))->toPrecision(2);
		$order->total_paid = (float) $totalPrice->toPrecision(2);
		$order->reference = $orderReference;
		$order->update();

		/** @var MemorizeCartService $memorizeCart */
		$memorizeCart = $this->module->getMollieContainer(MemorizeCartService::class);

		$memorizeCart->memorizeCart($order);
	}
}
