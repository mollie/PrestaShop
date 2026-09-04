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

namespace Mollie\Tests\Unit\Handler\Order;

use Mollie\Api\Resources\Payment;
use Mollie\Handler\Order\ReturnOrderCreationHandler;
use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\TransactionService;
use Mollie\Tests\Unit\BaseTestCase;

class ReturnOrderCreationHandlerTest extends BaseTestCase
{
    private const CART_ID = 75352;

    /** @var TransactionService */
    private $transactionService;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Lock */
    private $lock;

    /** @var LoggerInterface */
    private $logger;

    /** @var Payment */
    private $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionService = $this->mock(TransactionService::class);
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->lock = $this->mock(Lock::class);
        $this->logger = $this->mock(LoggerInterface::class);
        $this->transaction = $this->mock(Payment::class);
    }

    public function testItReturnsTheExistingOrderWithoutCreatingAnother(): void
    {
        $this->orderRepository->method('getOrderIdByCartId')->willReturn(21342);

        $this->lock->expects($this->never())->method('create');
        $this->transactionService->expects($this->never())->method('processTransaction');

        $this->assertSame(21342, $this->handler()->handle($this->transaction, self::CART_ID));
    }

    public function testItCreatesTheOrderWhenTheWebhookHasNotDoneItYet(): void
    {
        $this->orderRepository->method('getOrderIdByCartId')->willReturnOnConsecutiveCalls(0, 21343);

        $this->lock->expects($this->once())->method('create');
        $this->lock->method('acquire')->willReturn(true);
        $this->lock->expects($this->once())->method('release');

        $this->transactionService
            ->expects($this->once())
            ->method('processTransaction')
            ->with($this->transaction);

        $this->assertSame(21343, $this->handler()->handle($this->transaction, self::CART_ID));
    }

    public function testItLeavesCreationToTheRequestHoldingTheLock(): void
    {
        $this->orderRepository->method('getOrderIdByCartId')->willReturn(0);

        $this->lock->method('acquire')->willReturn(false);

        $this->transactionService->expects($this->never())->method('processTransaction');

        $this->assertSame(0, $this->handler()->handle($this->transaction, self::CART_ID));
    }

    public function testItReportsNoOrderWhenCreationFails(): void
    {
        $this->orderRepository->method('getOrderIdByCartId')->willReturn(0);

        $this->lock->method('acquire')->willReturn(true);

        $this->transactionService
            ->method('processTransaction')
            ->willThrowException(new \RuntimeException('Wrong cart amount'));

        $this->logger->expects($this->once())->method('error');

        $this->assertSame(0, $this->handler()->handle($this->transaction, self::CART_ID));
    }

    public function testItReportsNoOrderWhenTheLockCannotBeCreated(): void
    {
        $this->orderRepository->method('getOrderIdByCartId')->willReturn(0);

        $this->lock
            ->method('create')
            ->willThrowException(new \RuntimeException('No lock store available'));

        $this->transactionService->expects($this->never())->method('processTransaction');
        $this->logger->expects($this->once())->method('error');

        $this->assertSame(0, $this->handler()->handle($this->transaction, self::CART_ID));
    }

    private function handler(): ReturnOrderCreationHandler
    {
        return new ReturnOrderCreationHandler(
            $this->transactionService,
            $this->orderRepository,
            $this->lock,
            $this->logger
        );
    }
}
