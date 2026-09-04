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

use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\TransactionService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Creates the PrestaShop order for an already paid Mollie transaction when the
 * customer gets back before the webhook has been processed. Runs the same
 * webhook path so both routes cannot drift apart.
 */
class ReturnOrderCreationHandler
{
    const FILE_NAME = 'ReturnOrderCreationHandler';

    /** @var TransactionService */
    private $transactionService;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Lock */
    private $lock;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TransactionService $transactionService,
        OrderRepositoryInterface $orderRepository,
        Lock $lock,
        LoggerInterface $logger
    ) {
        $this->transactionService = $transactionService;
        $this->orderRepository = $orderRepository;
        $this->lock = $lock;
        $this->logger = $logger;
    }

    /**
     * @param \Mollie\Api\Resources\Order|\Mollie\Api\Resources\Payment $transaction
     *
     * @return int Order id, or 0 while the order still does not exist
     */
    public function handle($transaction, int $cartId): int
    {
        $orderId = $this->orderRepository->getOrderIdByCartId($cartId);

        if ($orderId) {
            return $orderId;
        }

        try {
            $this->lock->create(sprintf('return-order-%d', $cartId));

            if (!$this->lock->acquire()) {
                // The webhook or a parallel poll is creating it; report whatever exists now.
                return $this->orderRepository->getOrderIdByCartId($cartId);
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s - Failed to acquire lock', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
                'cart_id' => $cartId,
            ]);

            return 0;
        }

        try {
            $this->transactionService->processTransaction($transaction);

            $this->logger->info(sprintf('%s - Created order from return flow', self::FILE_NAME), [
                'transaction_id' => $transaction->id,
                'cart_id' => $cartId,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s - Failed to create order', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
                'cart_id' => $cartId,
            ]);
        }

        try {
            $this->lock->release();
        } catch (\Throwable $e) {
            // Lock auto-releases on destruct
        }

        return $this->orderRepository->getOrderIdByCartId($cartId);
    }
}
