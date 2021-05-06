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

use Exception;
use Mollie;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Order;
use PrestaShopLogger;

class MollieOrderInfoService
{
	/**
	 * @var PaymentMethodRepositoryInterface
	 */
	private $paymentMethodRepository;
	/**
	 * @var RefundService
	 */
	private $refundService;
	/**
	 * @var ShipService
	 */
	private $shipService;
	/**
	 * @var CancelService
	 */
	private $cancelService;
	/**
	 * @var ShipmentServiceInterface
	 */
	private $shipmentService;
	/**
	 * @var Mollie
	 */
	private $module;
	/**
	 * @var ApiService
	 */
	private $apiService;

	public function __construct(
		Mollie $module,
		PaymentMethodRepositoryInterface $paymentMethodRepository,
		RefundService $refundService,
		ShipService $shipService,
		CancelService $cancelService,
		ShipmentServiceInterface $shipmentService,
		ApiService $apiService
	) {
		$this->module = $module;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->refundService = $refundService;
		$this->shipService = $shipService;
		$this->cancelService = $cancelService;
		$this->shipmentService = $shipmentService;
		$this->apiService = $apiService;
	}

	/**
	 * @param array $input
	 *
	 * @return array
	 *
	 * @since 3.3.0
	 */
	public function displayMollieOrderInfo($input)
	{
		$transaction = $this->paymentMethodRepository->getPaymentBy('transaction_id', $input['transactionId']);
		$order = new Order($transaction['order_id']);
		$this->module->updateApiKey($order->id_shop);
		try {
			$mollieData = $this->paymentMethodRepository->getPaymentBy('transaction_id', $input['transactionId']);
			if ('payments' === $input['resource']) {
				switch ($input['action']) {
					case 'refund':
						if (!isset($input['amount']) || empty($input['amount'])) {
							// No amount = full refund
							$status = $this->refundService->doPaymentRefund($mollieData['transaction_id']);
						} else {
							$status = $this->refundService->doPaymentRefund($mollieData['transaction_id'], $input['amount']);
						}

						return [
							'success' => isset($status['status']) && 'success' === $status['status'],
							'payment' => $this->apiService->getFilteredApiPayment($this->module->api, $input['transactionId'], false),
						];
					case 'retrieve':
						return [
							'success' => true,
							'payment' => $this->apiService->getFilteredApiPayment($this->module->api, $input['transactionId'], false),
						];
					default:
						return ['success' => false];
				}
			} elseif ('orders' === $input['resource']) {
				switch ($input['action']) {
					case 'retrieve':
						$info = $this->paymentMethodRepository->getPaymentBy('transaction_id', $input['transactionId']);
						if (!$info) {
							return ['success' => false];
						}
						$tracking = $this->shipmentService->getShipmentInformation($info['order_reference']);

						return [
							'success' => true,
							'order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['transactionId']),
							'tracking' => $tracking,
						];
					case 'ship':
						$status = $this->shipService->doShipOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : [], isset($input['tracking']) ? $input['tracking'] : null);

						return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['transactionId'])]);
					case 'refund':
						$status = $this->refundService->doRefundOrderLines($input['order'], isset($input['orderLines']) ? $input['orderLines'] : []);

						return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['order']['id'])]);
					case 'cancel':
						$status = $this->cancelService->doCancelOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : []);

						return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['transactionId'])]);
					default:
						return ['success' => false];
				}
			}
		} catch (Exception $e) {
			PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");

			return ['success' => false];
		}

		return ['success' => false];
	}
}
