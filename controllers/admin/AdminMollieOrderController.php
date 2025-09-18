<?php
/**
 * Admin controller for handling Mollie order actions: refund, capture, ship.
 *
 * Follows PrestaShop module conventions and delegates business logic to services.
 */

use Mollie\Adapter\ToolsAdapter;
use Mollie\Logger\LoggerInterface;
use Mollie\Service\CaptureService;
use Mollie\Service\RefundService;
use Mollie\Service\ShipService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieOrderController extends ModuleAdminController
{
    const FILE_NAME = 'AdminMollieOrderController';

    /** @var Mollie */
    public $module;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function postProcess(): bool
    {
        if (!$this->context->employee->can('edit', 'AdminOrders')) {
            return false;
        }

        /** @var ToolsAdapter $tools */
        $tools = $this->module->getService(ToolsAdapter::class);
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);
        $cookie = \Context::getContext()->cookie;

        $orderId = $tools->getValueAsInt('orderId');
        $errors = json_decode($cookie->__get('mollie_order_management_errors'), false) ?: [];

        if ($tools->isSubmit('capture-order')) {
            try {
                $amount = (float) $tools->getValue('capture_amount');
                /** @var CaptureService $captureService */
                $captureService = $this->module->getService(CaptureService::class);
                $captureService->handleCapture($orderId, $amount);
            } catch (\Throwable $exception) {
                $errors[$orderId] = 'Capture failed. See logs.';
                $cookie->__set('mollie_order_management_errors', json_encode($errors));
                $logger->error('Failed to capture order.', [
                    'order_id' => $orderId,
                    'amount' => $amount ?? null,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        if ($tools->isSubmit('refund-order')) {
            try {
                $amount = (float) $tools->getValue('refund_amount');
                /** @var RefundService $refundService */
                $refundService = $this->module->getService(RefundService::class);
                $refundService->handleRefund($orderId, $amount);
            } catch (\Throwable $exception) {
                $errors[$orderId] = 'Refund failed. See logs.';
                $cookie->__set('mollie_order_management_errors', json_encode($errors));
                $logger->error('Failed to refund order.', [
                    'order_id' => $orderId,
                    'amount' => $amount ?? null,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        if ($tools->isSubmit('ship-order')) {
            try {
                /** @var ShipService $shipService */
                $shipService = $this->module->getService(ShipService::class);
                $shipService->handleShip($orderId);
            } catch (\Throwable $exception) {
                $errors[$orderId] = 'Shipping failed. See logs.';
                $cookie->__set('mollie_order_management_errors', json_encode($errors));
                $logger->error('Failed to ship order.', [
                    'order_id' => $orderId,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $this->redirectToOrderController('AdminOrders', $orderId);

        return true;
    }

    private function redirectToOrderController(string $controller, int $orderId): void
    {
        $url = \Context::getContext()->link->getAdminLink($controller, true, [], ['id_order' => $orderId, 'vieworder' => 1]);
        \Tools::redirectAdmin($url);
    }
}
