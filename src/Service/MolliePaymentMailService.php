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

namespace Mollie\Service;

use Cart;
use Context;
use Customer;
use Exception;
use Mollie;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\SecureKeyUtility;
use Mollie\Utility\TransactionUtility;
use Order;

class MolliePaymentMailService
{
	/**
	 * @var PaymentMethodRepository
	 */
	private $paymentMethodRepository;

	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(
		Mollie $module,
		PaymentMethodRepository $paymentMethodRepository,
		MailService $mailService
	) {
		$this->module = $module;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->mailService = $mailService;
	}

	public function sendSecondChanceMail($orderId)
	{
		$order = new Order($orderId);
		$payment = $this->paymentMethodRepository->getPaymentBy('order_id', $orderId);
		if (!$payment) {
			return false;
		}

		$response = [
			'success' => false,
			'message' => $this->module->l('Failed to created second chance email!'),
		];

		$transactionId = $payment['transaction_id'];

		$customer = new Customer($order->id_customer);

		$this->module->updateApiKey($order->id_shop);
		/** @var MollieApiClient $api */
		$api = $this->module->api;

		try {
			if (TransactionUtility::isOrderTransaction($transactionId)) {
				$response = $this->sendSecondChanceMailWithOrderAPI($api, $transactionId, $payment['method']);
			} else {
				$response = $this->sendSecondChanceMailWithPaymentApi($api, $transactionId);
			}
		} catch (Exception $exception) {
			$response['message'] = $this->module->l('Failed to create second chance email - API error');

			return $response;
		}

		if ($response['success']) {
			$this->mailService->sendSecondChanceMail($customer, $response['checkoutUrl'], $payment['method'], $order->id_shop);
		}

		return $response;
	}

	public function sendSecondChanceMailWithOrderAPI(MollieApiClient $api, $transactionId, $paymentMethod)
	{
		$orderApi = $api->orders->get($transactionId, ['embed' => 'payments']);
		if ($orderApi->isPaid() || $orderApi->isAuthorized() || $orderApi->isShipping() || $orderApi->isCompleted()) {
			return
				[
					'success' => false,
					'message' => $this->module->l('Failed to send second chance email! Order is already paid!'),
				];
		}

		$molliePayments = $orderApi->payments();
		$checkoutUrl = $this->getCheckoutUrl($molliePayments);

		if (!$checkoutUrl) {
			/** @var Payment $newPayment */
			$newPayment = $api->orders->get($transactionId)->createPayment(
				[
				]
			);
			$checkoutUrl = $newPayment->getCheckoutUrl();
		}

		return [
			'success' => true,
			'message' => $this->module->l('Second chance email was successfully send!'),
			'checkoutUrl' => $checkoutUrl,
		];
	}

	public function sendSecondChanceMailWithPaymentApi(MollieApiClient $api, $transactionId)
	{
		$context = Context::getContext();
		$paymentApi = $api->payments->get($transactionId);

		if ($paymentApi->isPaid() || $paymentApi->isAuthorized()) {
			return
				[
					'success' => false,
					'message' => $this->module->l('Failed to send second chance email! Order is already paid or expired!'),
				];
		}

		if (null !== $paymentApi->getCheckoutUrl()) {
			$checkoutUrl = $paymentApi->getCheckoutUrl();

			return [
				'success' => true,
				'message' => $this->module->l('Second chance email was successfully send!'),
				'checkoutUrl' => $checkoutUrl,
			];
		}

		$cart = new Cart($paymentApi->metadata->cart_id);
		$customer = new Customer($cart->id_customer);

		$key = SecureKeyUtility::generateReturnKey($customer->secure_key, $customer->id, $cart->id, $this->module->name);

		$paymentData = [
			'amount' => [
				'value' => $paymentApi->amount->value,
				'currency' => $paymentApi->amount->currency,
			],
			'redirectUrl' => $context->link->getModuleLink(
				'mollie',
				'return',
				[
					'cart_id' => $paymentApi->metadata->cart_id,
					'utm_nooverride' => 1,
					'rand' => time(),
					'key' => $key,
				],
				true
			),
			'description' => $paymentApi->description,
			'metadata' => [
				'cart_id' => $paymentApi->metadata->cart_id,
				'order_reference' => $paymentApi->metadata->order_reference,
				'secure_key' => $key,
			],
		];

		if (!EnvironmentUtility::isLocalEnvironment()) {
			$paymentData['webhookUrl'] = $context->link->getModuleLink(
				'mollie',
				'webhook',
				[],
				true
			);
		}
		$newPayment = $api->payments->create($paymentData);
		$updateTransactionId = $this->paymentMethodRepository->updateTransactionId($transactionId, $newPayment->id);

		if ($updateTransactionId) {
			$checkoutUrl = $newPayment->getCheckoutUrl();

			return [
				'success' => true,
				'message' => $this->module->l('Second chance email was successfully send!'),
				'checkoutUrl' => $checkoutUrl,
			];
		}
	}

	private function getCheckoutUrl($molliePayments)
	{
		$checkoutUrl = '';
		/** @var Payment $molliePayment */
		foreach ($molliePayments as $molliePayment) {
			if (PaymentStatus::STATUS_OPEN === $molliePayment->status ||
				PaymentStatus::STATUS_PENDING === $molliePayment->status
			) {
				return $molliePayment->getCheckoutUrl();
			}
		}

		return $checkoutUrl;
	}
}
