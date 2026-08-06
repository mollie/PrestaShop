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
use Db;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\OrderRepository;
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
    private const FILE_NAME = 'OrderStatusService';

    private const TRANSITION_MAX_ATTEMPTS = 3;
    private const TRANSITION_RETRY_DELAY_MS = 250;

    /**
     * @var MailService
     */
    private $mailService;

    private $orderRepository;

    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(MailService $mailService, OrderRepository $orderRepository, PrestaLoggerInterface $logger)
    {
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
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
        $orderHistory = $order->getHistory($order->id_lang, $statusId);

        if (!empty($orderHistory)) {
            return;
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        /*
         * current_state is committed before the history entry during a status transition,
         * so a matching current_state without a matching history entry means a previous
         * transition was interrupted mid-way and must be completed instead of skipped.
         */
        $currentStateApplied = !empty($order->getHistory($order->id_lang, (int) $order->current_state));
        $matchesCurrentState = (int) $order->current_state === (int) $statusId
            || ($this->isStatusPaid($statusId) && $this->isStatusPaid($order->current_state));

        if ($matchesCurrentState && $currentStateApplied) {
            return;
        }

        if (!$status) {
            return;
        }

        if ($matchesCurrentState) {
            $this->logger->error(sprintf('%s - Completing interrupted status transition', self::FILE_NAME), [
                'order_id' => (int) $order->id,
                'status_id' => (int) $statusId,
            ]);

            $this->repairInterruptedTransition($order);
        }

        if (null === $useExistingPayment) {
            $useExistingPayment = !$order->hasInvoice();
        }

        $orders = $this->orderRepository->findAllByCartId($order->id_cart);
        if (count($orders) > 1) {
            foreach ($orders as $subOrder) {
                $this->applyOrderStatus((int) $subOrder->id, (int) $statusId, $useExistingPayment, $status, $templateVars);
            }

            return;
        }

        $this->applyOrderStatus((int) $order->id, (int) $statusId, $useExistingPayment, $status, $templateVars);
    }

    private function applyOrderStatus(int $orderId, int $statusId, $useExistingPayment, string $status, array $templateVars): void
    {
        $history = null;

        $this->runTransitionWithRetry(function () use (&$history, $statusId, $orderId, $useExistingPayment) {
            $history = new OrderHistory();
            $history->id_order = $orderId;
            $history->changeIdOrderState($statusId, $orderId, $useExistingPayment);

            if (!$history->add()) {
                throw new PrestaShopException('Could not add order history entry');
            }
        }, $orderId, $statusId);

        $status = OrderStatusUtility::transformPaymentStatusToPaid($status, Config::STATUS_PAID_ON_BACKORDER);

        $order = new Order($orderId);

        if ($this->checkIfOrderConfNeedsToBeSend($statusId)) {
            $this->mailService->sendOrderConfMail($order, $statusId);
        }

        if ('0' !== Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($status))) {
            $history->sendEmail($order, $templateVars);
        }
    }

    /**
     * Runs the state transition inside a database transaction so an interruption
     * (deadlock, lock timeout, fatal) cannot leave the order half-updated, and
     * retries transient database errors instead of failing the webhook.
     */
    private function runTransitionWithRetry(callable $transition, int $orderId, int $statusId): void
    {
        $db = Db::getInstance();
        $attempt = 1;

        while (true) {
            $db->execute('START TRANSACTION');

            try {
                $transition();
                $db->execute('COMMIT');

                return;
            } catch (\Throwable $exception) {
                try {
                    $db->execute('ROLLBACK');
                } catch (\Throwable $rollbackException) {
                }

                if ($attempt >= self::TRANSITION_MAX_ATTEMPTS || !$this->isTransientDatabaseError($exception)) {
                    throw $exception;
                }

                $this->logger->error(sprintf('%s - Transient database error during status transition, retrying', self::FILE_NAME), [
                    'order_id' => $orderId,
                    'status_id' => $statusId,
                    'attempt' => $attempt,
                    'exception_message' => $exception->getMessage(),
                ]);

                usleep(self::TRANSITION_RETRY_DELAY_MS * 1000 * $attempt);
                ++$attempt;
            }
        }
    }

    private function isTransientDatabaseError(\Throwable $exception): bool
    {
        while ($exception !== null) {
            $message = $exception->getMessage();

            if (stripos($message, 'Deadlock found') !== false
                || stripos($message, 'Lock wait timeout') !== false
                || stripos($message, 'SQLSTATE[40001]') !== false
            ) {
                return true;
            }

            $exception = $exception->getPrevious();
        }

        return false;
    }

    /**
     * Completes what an interrupted transition left behind: an invoice without
     * linked order lines, payments and order invoice fields.
     */
    private function repairInterruptedTransition(Order $order): void
    {
        foreach ($order->getInvoicesCollection() as $invoice) {
            /** @var \OrderInvoice $invoice */
            $linkedDetails = (int) Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'order_detail` WHERE `id_order_invoice` = ' . (int) $invoice->id
            );

            if ($linkedDetails > 0) {
                continue;
            }

            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'order_detail` SET `id_order_invoice` = ' . (int) $invoice->id
                . ' WHERE `id_order` = ' . (int) $order->id . ' AND `id_order_invoice` = 0'
            );

            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment` (`id_order_invoice`, `id_order_payment`, `id_order`)'
                . ' SELECT ' . (int) $invoice->id . ', op.`id_order_payment`, ' . (int) $order->id
                . ' FROM `' . _DB_PREFIX_ . 'order_payment` op'
                . ' LEFT JOIN `' . _DB_PREFIX_ . 'order_invoice_payment` oip ON oip.`id_order_payment` = op.`id_order_payment`'
                . ' WHERE op.`order_reference` = \'' . pSQL($order->reference) . '\' AND oip.`id_order_payment` IS NULL'
            );

            if (!$order->invoice_number && $invoice->number) {
                $order->invoice_number = (int) $invoice->number;
                $order->invoice_date = (string) $invoice->date_add;
                $order->update();
            }
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
        if (!Configuration::get('PS_STOCK_MANAGEMENT')) {
            return false;
        }

        $order = new Order($orderId);
        $orderDetails = $order->getOrderDetailList();
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (self::isBackOrder(
                (bool) $orderDetail->getStockState(),
                (int) $orderDetail->product_quantity_in_stock,
                (int) $orderDetail->product_quantity
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decide whether an ordered product line is on backorder.
     *
     * The in-stock quantity is compared against the ordered quantity, not just
     * checked for a negative value. That catches the case where a product starts
     * at exactly 0 stock: the ordered quantity still exceeds what is available,
     * so the line is on backorder even though the stock never goes negative.
     *
     * @param bool $stockState out-of-stock flag from the order detail
     * @param int $quantityInStock quantity available when the order was placed
     * @param int $quantityOrdered quantity the customer ordered
     *
     * @return bool
     */
    public static function isBackOrder($stockState, $quantityInStock, $quantityOrdered)
    {
        return $stockState || $quantityInStock < $quantityOrdered;
    }

    private function isStatusPaid($statusId)
    {
        return ((int) $statusId === (int) Configuration::get(Config::MOLLIE_STATUS_PAID)) ||
            ((int) $statusId === (int) Configuration::get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID)) ||
            ((int) $statusId === (int) Configuration::get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED));
    }
}
