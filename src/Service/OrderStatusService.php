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

use Configuration;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Utility\OrderStatusUtility;
use Order;
use OrderDetail;
use OrderHistory;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderStatusService
{
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * @param int $orderId
     * @param string|int $statusId
     * @param null $useExistingPayment
     * @param array $templateVars
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.2 Accept both Order ID and Order object
     * @since 3.3.2 Accept both Mollie status string and PrestaShop status ID
     * @since 3.3.2 $useExistingPayment option
     * @since 3.3.4 Accepts template vars for the corresponding email template
     */
    public function setOrderStatus($orderId, $statusId, $useExistingPayment = null, $templateVars = [])
    {
        if (is_string($statusId)) {
            $status = $statusId;
            if (empty(Config::getStatuses()[$statusId])) {
                return;
            }
            $statusId = $this->transformOrderStatusToBackorder($statusId, $orderId);

            $statusId = (int) Config::getStatuses()[$statusId];
        } else {
            $status = '';
            foreach (Config::getStatuses() as $mollieStatus => $prestaShopStatusId) {
                if ((int) $prestaShopStatusId === $statusId) {
                    $status = $mollieStatus;
                    break;
                }
            }
        }

        if (0 === (int) $statusId) {
            return;
        }
        $order = new Order((int) $orderId);

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        if ((int) $order->current_state === (int) $statusId) {
            return;
        }
        if ($this->isStatusPaid($statusId) && $this->isStatusPaid($order->current_state)) {
            return;
        }

        if (!Validate::isLoadedObject($order)
            || !$status
        ) {
            return;
        }
        if (null === $useExistingPayment) {
            $useExistingPayment = !$order->hasInvoice();
        }

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($statusId, $orderId, $useExistingPayment);

        $status = OrderStatusUtility::transformPaymentStatusToPaid($status, Config::STATUS_PAID_ON_BACKORDER);

        if ($this->checkIfOrderConfNeedsToBeSend($statusId)) {
            $this->mailService->sendOrderConfMail($order, $statusId);
        }

        if ('0' === Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($status))) {
            $history->add();
        } else {
            $history->addWithemail(true, $templateVars);
        }
    }

    public function transformOrderStatusToBackorder($status, $orderId)
    {
        if (PaymentStatus::STATUS_PAID === $status || OrderStatus::STATUS_AUTHORIZED === $status) {
            if ($this->isOrderBackOrder($orderId)) {
                return Config::STATUS_PAID_ON_BACKORDER;
            }
        }

        return $status;
    }

    private function checkIfOrderConfNeedsToBeSend($statusId)
    {
        if (Config::NEW_ORDER_MAIL_SEND_ON_PAID !== (int) Configuration::get(Config::MOLLIE_SEND_ORDER_CONFIRMATION)) {
            return false;
        }

        return $this->isStatusPaid($statusId);
    }

    private function isOrderBackOrder($orderId)
    {
        $order = new Order($orderId);
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                Configuration::get('PS_STOCK_MANAGEMENT') &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
            ) {
                return true;
            }
        }

        return false;
    }

    private function isStatusPaid($statusId)
    {
        return ((int) $statusId === (int) Configuration::get(Config::MOLLIE_STATUS_PAID)) ||
            ((int) $statusId === (int) Configuration::get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID)) ||
            ((int) $statusId === (int) Configuration::get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED));
    }
}
