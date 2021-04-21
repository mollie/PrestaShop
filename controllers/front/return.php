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

use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Controller\AbstractMollieController;
use Mollie\Factory\CustomerFactory;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\MemorizeCartService;
use Mollie\Service\PaymentReturnService;
use Mollie\Service\RestorePendingCartService;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\PaymentMethodUtility;
use Mollie\Utility\TransactionUtility;
use Mollie\Validator\OrderCallBackValidator;

if (!defined('_PS_VERSION_')) {
	exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

class MollieReturnModuleFrontController extends AbstractMollieController
{
	/** @var Mollie */
	public $module;

	const FILE_NAME = 'return';

	/** @var bool */
	public $ssl = true;

	/**
	 * Unset the cart id from cookie if the order exists.
	 *
	 * @throws PrestaShopException
	 */
	public function init()
	{
		/** @var Context $context */
		$context = Context::getContext();
		/** @var Cart $cart */
		$cart = new Cart((int) $this->context->cookie->__get('id_cart'));
		if (Validate::isLoadedObject($cart) && !$cart->orderExists()) {
			unset($context->cart);
			unset($context->cookie->id_cart);
			unset($context->cookie->checkedTOS);
			unset($context->cookie->check_cgv);
		}

		parent::init();
	}

	/**
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws SmartyException
	 */
	public function initContent()
	{
		$idCart = (int) Tools::getValue('cart_id');
		$key = Tools::getValue('key');
		$context = Context::getContext();
		$customer = $context->customer;

		/** @var OrderCallBackValidator $orderCallBackValidator */
		$orderCallBackValidator = $this->module->getMollieContainer(OrderCallBackValidator::class);

		if (!$orderCallBackValidator->validate($key, $idCart)) {
			Tools::redirectLink('index.php');
		}

		/** @var CustomerFactory $customerFactory */
		$customerFactory = $this->module->getMollieContainer(CustomerFactory::class);
		$this->context = $customerFactory->recreateFromRequest($customer->id, $key, $this->context);
		if (Tools::getValue('ajax')) {
			$this->processAjax();
			exit;
		}

		parent::initContent();

		$data = [];
		$cart = null;

		/** @var PaymentMethodRepository $paymentMethodRepo */
		$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
		if (Tools::getIsset('cart_id')) {
			$idCart = (int) Tools::getValue('cart_id');

			// Check if user that's seeing this is the cart-owner
			$cart = new Cart($idCart);
			$data['auth'] = (int) $cart->id_customer === $customer->id;
			if ($data['auth']) {
				$data['mollie_info'] = $paymentMethodRepo->getPaymentBy('cart_id', (string) $idCart);
			}
		}

		if (isset($data['auth']) && $data['auth']) {
			// any paid payments for this cart?

			if (false === $data['mollie_info']) {
				$data['mollie_info'] = [];
				$data['msg_details'] = $this->module->l('The order with this id does not exist.', self::FILE_NAME);
			} elseif (PaymentMethod::BANKTRANSFER === $data['mollie_info']['method']
				&& PaymentStatus::STATUS_OPEN === $data['mollie_info']['bank_status']
			) {
				$data['msg_details'] = $this->module->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.', self::FILE_NAME);
			} else {
				$data['wait'] = true;
			}
		} else {
			// Not allowed? Don't make query but redirect.
			$data['mollie_info'] = [];
			$data['msg_details'] = $this->module->l('You are not authorised to see this page.', self::FILE_NAME);
			Tools::redirect(Context::getContext()->link->getPageLink('index', true));
		}

		$this->context->smarty->assign($data);
		$this->context->smarty->assign('link', $this->context->link);

		if (!empty($data['wait'])) {
			$this->context->smarty->assign(
				'checkStatusEndpoint',
				$this->context->link->getModuleLink(
					$this->module->name,
					'return',
					[
						'ajax' => 1,
						'action' => 'getStatus',
						'transaction_id' => $data['mollie_info']['transaction_id'],
						'key' => $key,
						'cart_id' => $idCart,
					],
					true
				)
			);
			$this->setTemplate('mollie_wait.tpl');
		} else {
			$this->setTemplate('mollie_return.tpl');
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

	/**
	 * @throws PrestaShopException
	 * @throws SmartyException
	 */
	protected function processAjax()
	{
		if (empty($this->context->customer->id)) {
			return;
		}

		switch (Tools::getValue('action')) {
			case 'getStatus':
				$this->processGetStatus();
				break;
		}

		exit;
	}

	/**
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function processGetStatus()
	{
		header('Content-Type: application/json;charset=UTF-8');
		/** @var PaymentMethodRepository $paymentMethodRepo */
		$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);

		$transactionId = Tools::getValue('transaction_id');
		$dbPayment = $paymentMethodRepo->getPaymentBy('transaction_id', $transactionId);
		$cart = new Cart($dbPayment['cart_id']);
		if (!Validate::isLoadedObject($cart)) {
			exit(json_encode([
				'success' => false,
			]));
		}
		$orderId = (int) Order::getOrderByCartId((int) $cart->id); /** @phpstan-ignore-line */
		$order = new Order((int) $orderId);

		if (!Validate::isLoadedObject($cart)) {
			exit(json_encode([
				'success' => false,
			]));
		}

		if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
			exit(json_encode([
				'success' => false,
			]));
		}

		if (!Tools::isSubmit('module')) {
			$_GET['module'] = $this->module->name;
		}

		$isOrder = TransactionUtility::isOrderTransaction($transactionId);
		if ($isOrder) {
			$transaction = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
		} else {
			$transaction = $this->module->api->payments->get($transactionId);
		}

		$orderStatus = $transaction->status;

		if ('order' === $transaction->resource) {
			$payments = ArrayUtility::getLastElement($transaction->_embedded->payments);
			$orderStatus = $payments->status;
		}

		$notSuccessfulPaymentMessage = $this->module->l('Your payment was not successful, please try again.', self::FILE_NAME);
		$paymentMethod = PaymentMethodUtility::getPaymentMethodName($transaction->method);

		/** @var PaymentReturnService $paymentReturnService */
		$paymentReturnService = $this->module->getMollieContainer(PaymentReturnService::class);
		switch ($orderStatus) {
			case PaymentStatus::STATUS_OPEN:
			case PaymentStatus::STATUS_PENDING:
				$response = $paymentReturnService->handleStatus(
					$order,
					$transaction,
					$paymentReturnService::PENDING
				);
				break;
			case PaymentStatus::STATUS_PAID:
			case PaymentStatus::STATUS_AUTHORIZED:
				$response = $paymentReturnService->handleStatus(
					$order,
					$transaction,
					$paymentReturnService::DONE
				);

				/** @var MemorizeCartService $memorizeCart */
				$memorizeCart = $this->module->getMollieContainer(MemorizeCartService::class);
				$memorizeCart->removeMemorizedCart($order);

				$order->total_paid_real = $transaction->amount->value;
				$order->update();
				break;
			case PaymentStatus::STATUS_EXPIRED:
			case PaymentStatus::STATUS_CANCELED:
			case PaymentStatus::STATUS_FAILED:
				$this->setWarning($notSuccessfulPaymentMessage);
				/** @var RestorePendingCartService $restorePendingCart */
				$restorePendingCart = $this->module->getMollieContainer(RestorePendingCartService::class);
				$restorePendingCart->restore($order);

				$response = $paymentReturnService->handleFailedStatus($order, $transaction, $paymentMethod);
				break;
			default:
				exit();
		}

		exit(json_encode($response));
	}

	private function setWarning($message)
	{
		/* @phpstan-ignore-next-line */
		$this->warning[] = $message;

		$this->context->cookie->__set('mollie_payment_canceled_error', json_encode($this->warning));
	}
}
