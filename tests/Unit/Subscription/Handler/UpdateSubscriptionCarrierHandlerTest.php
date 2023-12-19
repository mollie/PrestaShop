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

namespace Mollie\Tests\Unit\Subscription\Handler;

use Mollie\Exception\MollieException;
use Mollie\Service\MailService;
use Mollie\Subscription\Action\UpdateRecurringOrderAction;
use Mollie\Subscription\Action\UpdateSubscriptionAction;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\Handler\CloneOriginalSubscriptionCartHandler;
use Mollie\Subscription\Handler\UpdateSubscriptionCarrierHandler;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Tests\Unit\BaseTestCase;

class UpdateSubscriptionCarrierHandlerTest extends BaseTestCase
{
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var UpdateSubscriptionAction */
    private $updateSubscriptionAction;
    /** @var UpdateRecurringOrderAction */
    private $updateRecurringOrderAction;
    /** @var CloneOriginalSubscriptionCartHandler */
    private $cloneOriginalSubscriptionCartHandler;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;
    /** @var MailService */
    private $mailService;

    public function setUp(): void
    {
        parent::setUp();

        $this->recurringOrderRepository = $this->mock(RecurringOrderRepositoryInterface::class);
        $this->updateSubscriptionAction = $this->mock(UpdateSubscriptionAction::class);
        $this->updateRecurringOrderAction = $this->mock(UpdateRecurringOrderAction::class);
        $this->cloneOriginalSubscriptionCartHandler = $this->mock(CloneOriginalSubscriptionCartHandler::class);
        $this->subscriptionOrderAmountProvider = $this->mock(SubscriptionOrderAmountProvider::class);
        $this->mailService = $this->mock(MailService::class);
    }

    public function testItSuccessfullyHandles(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cart->id = 3;
        $this->cart->id_customer = 1;
        $this->cart->id_address_delivery = 5;
        $this->cart->id_currency = 1;

        $this->cart->expects($this->once())->method('getProducts')->willReturn([
            [
                'total_price_tax_incl' => 10.00,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willReturn($this->cart);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willReturn(
            new Amount(20.00, 'EUR')
        );

        $this->updateSubscriptionAction->expects($this->once())->method('run');

        $this->updateRecurringOrderAction->expects($this->once())->method('run');

        $this->mailService->expects($this->once())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertEmpty($result);
    }

    public function testItUnsuccessfullyHandlesMatchingCarrierId(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->never())->method('updateValue');

        $this->context->expects($this->never())->method('getShopId');

        $this->recurringOrderRepository->expects($this->never())->method('getAllOrdersBasedOnStatuses');

        $this->cloneOriginalSubscriptionCartHandler->expects($this->never())->method('run');

        $this->subscriptionOrderAmountProvider->expects($this->never())->method('get');

        $this->updateSubscriptionAction->expects($this->never())->method('run');

        $this->updateRecurringOrderAction->expects($this->never())->method('run');

        $this->mailService->expects($this->never())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(1);

        $this->assertEmpty($result);
    }

    public function testItUnsuccessfullyHandlesFailedToHandleOriginalSubscriptionCartCloning(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willThrowException(new MollieException('', 0));

        $this->subscriptionOrderAmountProvider->expects($this->never())->method('get');

        $this->updateSubscriptionAction->expects($this->never())->method('run');

        $this->updateRecurringOrderAction->expects($this->never())->method('run');

        $this->mailService->expects($this->never())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertCount(1, $result);
    }

    public function testItUnsuccessfullyHandlesFailedToProvideSubscriptionOrderAmount(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cart->id = 3;
        $this->cart->id_customer = 1;
        $this->cart->id_address_delivery = 5;
        $this->cart->id_currency = 1;

        $this->cart->expects($this->once())->method('getProducts')->willReturn([
            [
                'total_price_tax_incl' => 10.00,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willReturn($this->cart);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willThrowException(new MollieException('', 0));

        $this->updateSubscriptionAction->expects($this->never())->method('run');

        $this->updateRecurringOrderAction->expects($this->never())->method('run');

        $this->mailService->expects($this->never())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertCount(1, $result);
    }

    public function testItUnsuccessfullyHandlesFailedToUpdateSubscription(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cart->id = 3;
        $this->cart->id_customer = 1;
        $this->cart->id_address_delivery = 5;
        $this->cart->id_currency = 1;

        $this->cart->expects($this->once())->method('getProducts')->willReturn([
            [
                'total_price_tax_incl' => 10.00,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willReturn($this->cart);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willReturn(
            new Amount(20.00, 'EUR')
        );

        $this->updateSubscriptionAction->expects($this->once())->method('run')->willThrowException(new MollieException('', 0));

        $this->updateRecurringOrderAction->expects($this->never())->method('run');

        $this->mailService->expects($this->never())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertCount(1, $result);
    }

    public function testItUnsuccessfullyHandlesFailedToUpdateRecurringOrder(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cart->id = 3;
        $this->cart->id_customer = 1;
        $this->cart->id_address_delivery = 5;
        $this->cart->id_currency = 1;

        $this->cart->expects($this->once())->method('getProducts')->willReturn([
            [
                'total_price_tax_incl' => 10.00,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willReturn($this->cart);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willReturn(
            new Amount(20.00, 'EUR')
        );

        $this->updateSubscriptionAction->expects($this->once())->method('run');

        $this->updateRecurringOrderAction->expects($this->once())->method('run')->willThrowException(new MollieException('', 0));

        $this->mailService->expects($this->never())->method('sendSubscriptionCarrierUpdateMail');

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertCount(1, $result);
    }

    public function testItUnsuccessfullyHandlesFailedToSendSubscriptionCarrierUpdateMail(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(1);
        $this->configuration->expects($this->once())->method('updateValue');

        $this->context->expects($this->once())->method('getShopId')->willReturn(1);

        $this->recurringOrderRepository->expects($this->once())->method('getAllOrdersBasedOnStatuses')->willReturn([
            [
                'id' => 1,
                'mollie_customer_id' => 'test-mollie-customer-id',
                'mollie_subscription_id' => 'test-mollie-subscription-id',
                'id_cart' => 2,
                'id_recurring_product' => 3,
                'id_invoice_address' => 4,
                'id_delivery_address' => 5,
            ],
        ]);

        $this->cart->id = 3;
        $this->cart->id_customer = 1;
        $this->cart->id_address_delivery = 5;
        $this->cart->id_currency = 1;

        $this->cart->expects($this->once())->method('getProducts')->willReturn([
            [
                'total_price_tax_incl' => 10.00,
            ],
        ]);

        $this->cloneOriginalSubscriptionCartHandler->expects($this->once())->method('run')->willReturn($this->cart);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willReturn(
            new Amount(20.00, 'EUR')
        );

        $this->updateSubscriptionAction->expects($this->once())->method('run');

        $this->updateRecurringOrderAction->expects($this->once())->method('run');

        $this->mailService->expects($this->once())->method('sendSubscriptionCarrierUpdateMail')->willThrowException(new MollieException('', 0));

        $updateSubscriptionCarrierHandler = new UpdateSubscriptionCarrierHandler(
            $this->configuration,
            $this->recurringOrderRepository,
            $this->context,
            $this->updateSubscriptionAction,
            $this->updateRecurringOrderAction,
            $this->logger,
            $this->cloneOriginalSubscriptionCartHandler,
            $this->subscriptionOrderAmountProvider,
            $this->mailService
        );

        $result = $updateSubscriptionCarrierHandler->run(99);

        $this->assertCount(1, $result);
    }
}
