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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\ApiService;
use Mollie\Service\PaymentMethodService;
use Mollie\Service\TransactionService;
use Mollie\Utility\EnvironmentUtility;

if (!defined('_PS_VERSION_')) {
	exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

/**
 * TODO check if this is even used as IDEAL QRcode has not worked for a long time.
 * Class MollieQrcodeModuleFrontController.
 *
 * @property Mollie $module
 */
class MollieQrcodeModuleFrontController extends ModuleFrontController
{
	const PENDING = 1;
	const SUCCESS = 2;
	const REFRESH = 3;

	/** @var bool */
	public $ssl = true;
	/** @var bool If false, does not build left page column content and hides it. */
	public $display_column_left = false;
	/** @var bool If false, does not build right page column content and hides it. */
	public $display_column_right = false;

	/**
	 * @throws ApiException
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws SmartyException
	 */
	public function initContent()
	{
		if (Tools::getValue('ajax')) {
			$this->processAjax();
			exit;
		}

		if (Tools::getValue('done')) {
			$canceled = true;
			/** @var PaymentMethodRepository $paymentMethodRepo */
			$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
			$dbPayment = $paymentMethodRepo->getPaymentBy('cart_id', Tools::getValue('cart_id'));
			if (is_array($dbPayment)) {
				try {
					$apiPayment = $this->module->api->payments->get($dbPayment['transaction_id']);
					$canceled = PaymentStatus::STATUS_PAID !== $apiPayment->status;
				} catch (ApiException $e) {
				}
			}

			header('Content-Type: text/html');
			$this->context->smarty->assign([
				'ideal_logo' => __PS_BASE_URI__ . 'modules/mollie/views/img/ideal_logo.png',
				'canceled' => $canceled,
			]);
			echo $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mollie/views/templates/front/qr_done.tpl');
			exit;
		}
	}

	/**
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws SmartyException
	 */
	protected function processAjax()
	{
		switch (Tools::getValue('action')) {
//            case 'qrCodeNew':
//                return $this->processNewQrCode();
			case 'qrCodeStatus':
				return $this->processGetStatus();
			case 'cartAmount':
				return $this->processCartAmount();
		}

		exit;
	}

	/**
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function processNewQrCode()
	{
		header('Content-Type: application/json;charset=UTF-8');
		/** @var Mollie $mollie */
		$mollie = Module::getInstanceByName('mollie');
		$context = Context::getContext();
		$customer = $context->customer;
		$cart = $context->cart;
		if (!$cart instanceof Cart || !$cart->getOrderTotal(true)) {
			exit(json_encode([
				'success' => false,
				'message' => 'No active cart',
			]));
		}
		/** @var PaymentMethodRepository $paymentMethodRepo */
		$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
		/** @var ApiService $apiService */
		$apiService = $this->module->getMollieContainer(ApiService::class);
		/** @var PaymentMethodService $paymentMethodService */
		$paymentMethodService = $this->module->getMollieContainer(PaymentMethodService::class);

		$orderTotal = $cart->getOrderTotal(true);
		$environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
		$paymentMethodId = $paymentMethodRepo->getPaymentMethodIdByMethodId(PaymentMethod::IDEAL, $environment);
		$paymentMethodObj = new MolPaymentMethod($paymentMethodId);
		$payment = $mollie->api->{$apiService->selectedApi(Mollie::$selectedApi)}->create($paymentMethodService->getPaymentData(
			$orderTotal,
			Tools::strtoupper($this->context->currency->iso_code),
			PaymentMethod::IDEAL,
			null,
			(int) $cart->id,
			$customer->secure_key,
			$paymentMethodObj,
			true
		), [
			'include' => 'details.qrCode',
		]);

		try {
			Db::getInstance()->insert(
				'mollie_payments',
				[
					'cart_id' => (int) $cart->id,
					'method' => pSQL($payment->method),
					'transaction_id' => pSQL($payment->id),
					'bank_status' => PaymentStatus::STATUS_OPEN,
					'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
				]
			);
		} catch (PrestaShopDatabaseException $e) {
			$paymentMethodRepo->tryAddOrderReferenceColumn();
			throw $e;
		}

		$src = isset($payment->details->qrCode->src) ? $payment->details->qrCode->src : null;
		exit(json_encode([
			'success' => (bool) $src,
			'href' => $src,
			'idTransaction' => $payment->id,
			'expires' => strtotime($payment->expiresAt) * 1000,
			'amount' => (int) ($orderTotal * 100),
		]));
	}

	/**
	 * @throws ApiException
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function processGetStatus()
	{
		header('Content-Type: application/json;charset=UTF-8');
		if (empty($this->context->cart)) {
			exit(json_encode([
				'success' => false,
				'status' => false,
				'amount' => null,
			]));
		}

		if (EnvironmentUtility::isLocalEnvironment()) {
			/** @var MolliePaymentAlias | MollieOrderAlias $apiPayment */
			$apiPayment = $this->module->api->payments->get(Tools::getValue('transaction_id'));
			if (!Tools::isSubmit('module')) {
				$_GET['module'] = $this->module->name;
			}
			/** @var TransactionService $transactionService */
			$transactionService = $this->module->getMollieContainer(TransactionService::class);

			$transactionService->processTransaction($apiPayment);
		}

		try {
			/** @var PaymentMethodRepository $paymentMethodRepo */
			$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
			$payment = $paymentMethodRepo->getPaymentBy('transaction_id', Tools::getValue('transaction_id'));
		} catch (PrestaShopDatabaseException $e) {
			exit(json_encode([
				'success' => false,
				'status' => false,
				'amount' => null,
			]));
		} catch (PrestaShopException $e) {
			exit(json_encode([
				'success' => false,
				'status' => false,
				'amount' => null,
			]));
		}

		switch ($payment['bank_status']) {
			case PaymentStatus::STATUS_PAID:
			case PaymentStatus::STATUS_AUTHORIZED:
				$status = static::SUCCESS;
				break;
			case PaymentStatus::STATUS_OPEN:
				$status = static::PENDING;
				break;
			default:
				$status = static::REFRESH;
				break;
		}

		$cart = new Cart($payment['cart_id']);
		$amount = (int) ($cart->getOrderTotal(true) * 100);
		exit(json_encode([
			'success' => true,
			'status' => $status,
			'amount' => $amount,
			'href' => $this->context->link->getPageLink(
				'order-confirmation',
				true,
				null,
				[
					'id_cart' => (int) $cart->id,
					'id_module' => (int) $this->module->id,
					'id_order' => Order::getOrderByCartId((int) $cart->id),
					'key' => $cart->secure_key,
				]
			),
		]));
	}

	/**
	 * @throws Exception
	 */
	protected function processCartAmount()
	{
		header('Content-Type: application/json;charset=UTF-8');
		/** @var Context $context */
		$context = Context::getContext();
		/** @var Cart $cart */
		$cart = $context->cart;

		$cartTotal = (int) ($cart->getOrderTotal(true) * 100);
		exit(json_encode([
			'success' => true,
			'amount' => $cartTotal,
		]));
	}
}
