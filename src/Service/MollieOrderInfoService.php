<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Exception;
use Mollie;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderInfoService
{
    const FILE_NAME = 'MollieOrderInfoService';

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
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ModuleFactory $module,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        RefundService $refundService,
        ShipService $shipService,
        CancelService $cancelService,
        ShipmentServiceInterface $shipmentService,
        ApiService $apiService,
        LoggerInterface $logger
    ) {
        $this->module = $module->getModule();
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->refundService = $refundService;
        $this->shipService = $shipService;
        $this->cancelService = $cancelService;
        $this->shipmentService = $shipmentService;
        $this->apiService = $apiService;
        $this->logger = $logger;
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
        $transactionId = isset($input['transactionId']) ? $input['transactionId'] : $input['order']['id'];
        $transaction = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transactionId);
        $order = new Order($transaction['order_id']);

        $this->module->updateApiKey((int) $order->id_shop);

        if (!$this->module->getApiClient()) {
            return ['success' => false];
        }
        try {
            if ('payments' === $input['resource']) {
                switch ($input['action']) {
                    case 'refund':
                        if (!isset($input['amount']) || empty($input['amount'])) {
                            // No amount = full refund
                            $status = $this->refundService->doPaymentRefund($transactionId);
                        } else {
                            $status = $this->refundService->doPaymentRefund($transactionId, $input['amount']);
                        }

                        return [
                            'success' => isset($status['status']) && 'success' === $status['status'],
                            'payment' => $this->apiService->getFilteredApiPayment($this->module->getApiClient(), $transactionId, false),
                        ];
                    case 'retrieve':
                        return [
                            'success' => true,
                            'payment' => $this->apiService->getFilteredApiPayment($this->module->getApiClient(), $transactionId, false),
                        ];
                    default:
                        return ['success' => false];
                }
            } elseif ('orders' === $input['resource']) {
                switch ($input['action']) {
                    case 'retrieve':
                        $info = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transactionId);
                        if (!$info) {
                            return ['success' => false];
                        }
                        $tracking = $this->shipmentService->getShipmentInformation($info['order_reference']);

                        return [
                            'success' => true,
                            'order' => $this->apiService->getFilteredApiOrder($this->module->getApiClient(), $transactionId),
                            'tracking' => $tracking,
                        ];
                    case 'ship':
                        $status = $this->shipService->doShipOrderLines($transactionId, isset($input['orderLines']) ? $input['orderLines'] : [], isset($input['tracking']) ? $input['tracking'] : null);

                        return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->getApiClient(), $transactionId)]);
                    case 'refund':
                        $status = $this->refundService->doRefundOrderLines($input['order'], isset($input['orderLines']) ? $input['orderLines'] : []);

                        return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->getApiClient(), $input['order']['id'])]);
                    case 'cancel':
                        $status = $this->cancelService->doCancelOrderLines($transactionId, isset($input['orderLines']) ? $input['orderLines'] : []);

                        return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->getApiClient(), $transactionId)]);
                    default:
                        return ['success' => false];
                }
            }
        } catch (Exception $e) {
            $this->logger->error(sprintf('%s - Failed to display Mollie order info: %s', self::FILE_NAME, $e->getMessage()), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return ['success' => false];
        }

        return ['success' => false];
    }
}
