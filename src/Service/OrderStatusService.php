<?php

namespace Mollie\Service;

use Configuration;
use Context;
use Mollie\Config\Config;
use Order;
use OrderHistory;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Validate;

class OrderStatusService
{
    /**
     * @param int $order
     * @param string|int $statusId
     * @param null $useExistingPayment
     * @param array $templateVars
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 3.3.2 Accept both Order ID and Order object
     * @since 3.3.2 Accept both Mollie status string and PrestaShop status ID
     * @since 3.3.2 $useExistingPayment option
     * @since 3.3.4 Accepts template vars for the corresponding email template
     */
    public function setOrderStatus($order, $statusId, $useExistingPayment = null, $templateVars = [])
    {
        if (is_string($statusId)) {
            $status = $statusId;
            if (empty(Config::getStatuses()[$statusId])) {
                return;
            }
            $statusId = (int)Config::getStatuses()[$statusId];
        } else {
            $status = '';
            foreach (Config::getStatuses() as $mollieStatus => $prestaShopStatusId) {
                if ((int)$prestaShopStatusId === $statusId) {
                    $status = $mollieStatus;
                    break;
                }
            }
        }

        if ((int) $statusId === 0) {
            return;
        }

        if (!$order instanceof Order) {
            $order = new Order((int)$order);
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $history = array_map(function ($item) {
            return (int)$item['id_order_state'];
        }, $order->getHistory(Context::getContext()->language->id));
        if (!Validate::isLoadedObject($order)
            || !$status
        ) {
            return;
        }
        if ($useExistingPayment === null) {
            $useExistingPayment = !$order->hasInvoice();
        }

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($statusId, $order, $useExistingPayment);

        if (Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($status))) {
            $history->addWithemail(true, $templateVars);
        } else {
            $history->add();
        }
    }

}