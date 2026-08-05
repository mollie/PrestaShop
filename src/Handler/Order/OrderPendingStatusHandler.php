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

namespace Mollie\Handler\Order;

use Mollie;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Factory\ModuleFactory;
use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderPendingStatusHandler
{
    const FILE_NAME = 'OrderPendingStatusHandler';

    /** Customer returned while Mollie payment is still open — no order created, redirect to payment step. */
    const ACTION_OPEN = 'open';

    /** Payment is pending processing — pending PS order created, redirect to order confirmation. */
    const ACTION_CONFIRM = 'confirm';

    /** Payment is paid/authorized — show wait spinner until webhook creates/updates order. */
    const ACTION_POLL = 'poll';

    /** Payment failed/expired/canceled — redirect customer to cart. */
    const ACTION_FAILED = 'failed';

    /** @var Mollie */
    private $module;
    /** @var OrderCreationHandler */
    private $orderCreationHandler;
    /** @var Lock */
    private $lock;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(
        ModuleFactory $moduleFactory,
        OrderCreationHandler $orderCreationHandler,
        Lock $lock,
        PrestaLoggerInterface $logger,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->module = $moduleFactory->getModule();
        $this->orderCreationHandler = $orderCreationHandler;
        $this->lock = $lock;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Checks live Mollie status for a payment that arrived at return.php with open/pending DB status.
     *
     * @return string One of ACTION_OPEN, ACTION_CONFIRM, ACTION_POLL, ACTION_FAILED
     */
    public function handle(string $transactionId, int $cartId): string
    {
        $existingOrderId = $this->orderRepository->getOrderIdByCartId($cartId);

        $isOrder = TransactionUtility::isOrderTransaction($transactionId);
        if ($isOrder) {
            $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
        } else {
            $transaction = $this->module->getApiClient()->payments->get($transactionId);
        }

        $paymentStatus = $transaction->status;
        if ('order' === $transaction->resource) {
            $payments = ArrayUtility::getLastElement($transaction->_embedded->payments);
            $paymentStatus = $payments->status;
        }

        $this->logger->info(sprintf('%s - Pending payment API status check', self::FILE_NAME), [
            'transaction_id' => $transactionId,
            'mollie_status' => $paymentStatus,
            'cart_id' => $cartId,
            'order_exists' => $existingOrderId > 0,
        ]);

        switch ($paymentStatus) {
            case PaymentStatus::STATUS_OPEN:
                return self::ACTION_OPEN;

            case PaymentStatus::STATUS_PENDING:
                if (!$existingOrderId) {
                    $this->createPendingOrder($transaction, $cartId);
                }

                return self::ACTION_CONFIRM;

            case PaymentStatus::STATUS_PAID:
            case PaymentStatus::STATUS_AUTHORIZED:
                return self::ACTION_POLL;

            case PaymentStatus::STATUS_EXPIRED:
            case PaymentStatus::STATUS_CANCELED:
            case PaymentStatus::STATUS_FAILED:
                return self::ACTION_FAILED;

            default:
                return self::ACTION_POLL;
        }
    }

    /**
     * @param \Mollie\Api\Resources\Order|\Mollie\Api\Resources\Payment $transaction
     */
    private function createPendingOrder($transaction, int $cartId): void
    {
        try {
            $this->lock->create(sprintf('pending-order-%d', $cartId));
            if (!$this->lock->acquire()) {
                return;
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s - Failed to acquire lock', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return;
        }

        try {
            $orderId = $this->orderCreationHandler->createPendingOrder($transaction, $cartId);

            $this->logger->info(sprintf('%s - Created pending order', self::FILE_NAME), [
                'order_id' => $orderId,
                'cart_id' => $cartId,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s - Failed to create pending order', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
        }

        try {
            $this->lock->release();
        } catch (\Throwable $e) {
            // Lock auto-releases on destruct
        }
    }
}
