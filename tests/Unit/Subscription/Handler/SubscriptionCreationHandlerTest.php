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

use Mollie\Api\Resources\Subscription;
use Mollie\Subscription\Action\CreateRecurringOrderAction;
use Mollie\Subscription\Action\CreateRecurringOrdersProductAction;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\CreateSubscriptionData;
use Mollie\Subscription\Exception\CouldNotCreateSubscription;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Handler\SubscriptionCreationHandler;
use Mollie\Subscription\Provider\SubscriptionProductProvider;
use Mollie\Subscription\Validator\SubscriptionSettingsValidator;
use Mollie\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SubscriptionCreationHandlerTest extends BaseTestCase
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var CreateSubscriptionDataFactory */
    private $createSubscriptionDataFactory;
    /** @var SubscriptionSettingsValidator */
    private $subscriptionSettingsValidator;
    /** @var CreateRecurringOrdersProductAction */
    private $createRecurringOrdersProductAction;
    /** @var CreateRecurringOrderAction */
    private $createRecurringOrderAction;
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionApi = $this->mock(SubscriptionApi::class);
        $this->createSubscriptionDataFactory = $this->mock(CreateSubscriptionDataFactory::class);
        $this->subscriptionSettingsValidator = $this->mock(SubscriptionSettingsValidator::class);
        $this->createRecurringOrdersProductAction = $this->mock(CreateRecurringOrdersProductAction::class);
        $this->createRecurringOrderAction = $this->mock(CreateRecurringOrderAction::class);
        $this->subscriptionProductProvider = $this->mock(SubscriptionProductProvider::class);
    }

    public function testItSuccessfullyHandlesSubscriptionCreation(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);
        $order->id = 1;
        $order->id_cart = 1;
        $order->id_currency = 1;
        $order->id_customer = 1;
        $order->id_address_delivery = 1;
        $order->id_address_invoice = 1;

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn($products[0]);

        $subscriptionData = $this->mock(CreateSubscriptionData::class);

        $this->createSubscriptionDataFactory->expects($this->once())->method('build')->willReturn($subscriptionData);

        $subscriptionAmount = new \stdClass();
        $subscriptionAmount->value = 19.99;

        /** @var Subscription|MockObject $subscription */
        $subscription = $this->mock(Subscription::class);
        $subscription->description = 'test-description';
        $subscription->status = 'test-status';
        $subscription->amount = $subscriptionAmount;
        $subscription->nextPaymentDate = '2023-09-09 12:00:00';
        $subscription->canceledAt = '2023-09-10 12:00:00';
        $subscription->id = 'test-subscription-id';
        $subscription->customerId = 'test-customer-id';

        $this->subscriptionApi->expects($this->once())->method('subscribeOrder')->willReturn($subscription);

        /** @var \MolRecurringOrdersProduct|MockObject $recurringOrdersProduct */
        $recurringOrdersProduct = $this->mock(\MolRecurringOrdersProduct::class);
        $recurringOrdersProduct->id = 1;

        $this->createRecurringOrdersProductAction->expects($this->once())->method('run')->willReturn($recurringOrdersProduct);

        $this->createRecurringOrderAction->expects($this->once())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationInvalidSubscriptionSettings(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate')->willThrowException(new MollieSubscriptionException());

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->never())->method('getCartProducts');

        $this->subscriptionProductProvider->expects($this->never())->method('getProduct');

        $this->createSubscriptionDataFactory->expects($this->never())->method('build');

        $this->subscriptionApi->expects($this->never())->method('subscribeOrder');

        $this->createRecurringOrdersProductAction->expects($this->never())->method('run');

        $this->createRecurringOrderAction->expects($this->never())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_INVALID_SUBSCRIPTION_SETTINGS);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationFailedToFindSubscriptionProducts(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn([]);

        $this->createSubscriptionDataFactory->expects($this->never())->method('build');

        $this->subscriptionApi->expects($this->never())->method('subscribeOrder');

        $this->createRecurringOrdersProductAction->expects($this->never())->method('run');

        $this->createRecurringOrderAction->expects($this->never())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationFailedToCreateSubscriptionData(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn($products[0]);

        $this->createSubscriptionDataFactory->expects($this->once())->method('build')->willThrowException(new MollieSubscriptionException());

        $this->subscriptionApi->expects($this->never())->method('subscribeOrder');

        $this->createRecurringOrdersProductAction->expects($this->never())->method('run');

        $this->createRecurringOrderAction->expects($this->never())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_CREATE_SUBSCRIPTION_DATA);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationFailedToSubscribeOrder(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn($products[0]);

        $subscriptionData = $this->mock(CreateSubscriptionData::class);

        $this->createSubscriptionDataFactory->expects($this->once())->method('build')->willReturn($subscriptionData);

        $this->subscriptionApi->expects($this->once())->method('subscribeOrder')->willThrowException(new MollieSubscriptionException());

        $this->createRecurringOrdersProductAction->expects($this->never())->method('run');

        $this->createRecurringOrderAction->expects($this->never())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_SUBSCRIBE_ORDER);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationFailedToCreateRecurringOrdersProduct(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn($products[0]);

        $subscriptionData = $this->mock(CreateSubscriptionData::class);

        $this->createSubscriptionDataFactory->expects($this->once())->method('build')->willReturn($subscriptionData);

        /** @var Subscription|MockObject $subscription */
        $subscription = $this->mock(Subscription::class);

        $this->subscriptionApi->expects($this->once())->method('subscribeOrder')->willReturn($subscription);

        $this->createRecurringOrdersProductAction->expects($this->once())->method('run')->willThrowException(new MollieSubscriptionException());

        $this->createRecurringOrderAction->expects($this->never())->method('run');

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_CREATE_RECURRING_ORDERS_PRODUCT);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    public function testItUnsuccessfullyHandlesSubscriptionCreationFailedToCreateRecurringOrder(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $products = [
            [
                'id_product' => 1,
                'id_product_attribute' => 1,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
            [
                'id_product' => 2,
                'id_product_attribute' => 2,
                'product_quantity' => 1,
                'unit_price_tax_excl' => 19.99,
            ],
        ];

        /** @var \Order|MockObject $order */
        $order = $this->mock(\Order::class);

        $order->expects($this->once())->method('getCartProducts')->willReturn($products);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn($products[0]);

        $subscriptionData = $this->mock(CreateSubscriptionData::class);

        $this->createSubscriptionDataFactory->expects($this->once())->method('build')->willReturn($subscriptionData);

        $subscriptionAmount = new \stdClass();
        $subscriptionAmount->value = 19.99;

        /** @var Subscription|MockObject $subscription */
        $subscription = $this->mock(Subscription::class);
        $subscription->description = 'test-description';
        $subscription->status = 'test-status';
        $subscription->amount = $subscriptionAmount;
        $subscription->nextPaymentDate = '2023-09-09 12:00:00';
        $subscription->canceledAt = '2023-09-10 12:00:00';
        $subscription->id = 'test-subscription-id';
        $subscription->customerId = 'test-customer-id';

        $this->subscriptionApi->expects($this->once())->method('subscribeOrder')->willReturn($subscription);

        /** @var \MolRecurringOrdersProduct|MockObject $recurringOrdersProduct */
        $recurringOrdersProduct = $this->mock(\MolRecurringOrdersProduct::class);
        $recurringOrdersProduct->id = 1;

        $this->createRecurringOrdersProductAction->expects($this->once())->method('run')->willReturn($recurringOrdersProduct);

        $this->createRecurringOrderAction->expects($this->once())->method('run')->willThrowException(new MollieSubscriptionException());

        $subscriptionCreationHandler = new SubscriptionCreationHandler(
            $this->subscriptionApi,
            $this->createSubscriptionDataFactory,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotCreateSubscription::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_CREATE_RECURRING_ORDER);

        $subscriptionCreationHandler->handle($order, 'test-method');
    }
}
