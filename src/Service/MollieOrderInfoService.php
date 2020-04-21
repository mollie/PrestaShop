<?php


namespace Mollie\Service;


use Context;
use Exception;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use PrestaShopLogger;
use Profile;

class MollieOrderInfoService
{

    /**
     * @var PaymentMethodRepository
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
     * @var ShipmentService
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
        PaymentMethodRepository $paymentMethodRepository,
        RefundService $refundService,
        ShipService $shipService,
        CancelService $cancelService,
        ShipmentService $shipmentService,
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
     * @param $input
     * @return array
     * @since 3.3.0
     */
    public function displayMollieOrderInfo($input, $adminOrdersControllerId)
    {
        $context = Context::getContext();

        try {
            $mollieData = $this->paymentMethodRepository->getPaymentBy('transaction_id', $input['transactionId']);
            $access = Profile::getProfileAccess($context->employee->id_profile, $adminOrdersControllerId);
            if ($input['resource'] === 'payments') {
                switch ($input['action']) {
                    case 'refund':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => $this->module->l('You do not have permission to refund payments'),
                            ];
                        }
                        if (!isset($input['amount']) || empty($input['amount'])) {
                            // No amount = full refund
                            $status = $this->refundService->doPaymentRefund($mollieData['transaction_id']);
                        } else {
                            $status = $this->refundService->doPaymentRefund($mollieData['transaction_id'], $input['amount']);
                        }

                        return [
                            'success' => isset($status['status']) && $status['status'] === 'success',
                            'payment' => $this->apiService->getFilteredApiPayment($this->module->api, $input['transactionId'], false),
                        ];
                    case 'retrieve':
                        // Check order view permissions
                        if (!$access || empty($access['view'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->module->l('You do not have permission to %s payments'), $this->module->l('view')),
                            ];
                        }
                        return [
                            'success' => true,
                            'payment' => $this->apiService->getFilteredApiPayment($this->module->api, $input['transactionId'], false)
                        ];
                    default:
                        return ['success' => false];
                }
            } elseif ($input['resource'] === 'orders') {
                switch ($input['action']) {
                    case 'retrieve':
                        // Check order edit permissions
                        if (!$access || empty($access['view'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->module->l('You do not have permission to %s payments'), $this->module->l('edit')),
                            ];
                        }
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
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->module->l('You do not have permission to %s payments'), $this->module->l('ship')),
                            ];
                        }
                        $status = $this->shipmentService->doShipOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : [], isset($input['tracking']) ? $input['tracking'] : null);
                        return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['transactionId'])]);
                    case 'refund':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->module->l('You do not have permission to %s payments'), $this->module->l('refund')),
                            ];
                        }
                        $status = $this->refundService->doRefundOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : []);
                        return array_merge($status, ['order' => $this->apiService->getFilteredApiOrder($this->module->api, $input['transactionId'])]);
                    case 'cancel':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->module->l('You do not have permission to %s payments'), $this->module->l('cancel')),
                            ];
                        }
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