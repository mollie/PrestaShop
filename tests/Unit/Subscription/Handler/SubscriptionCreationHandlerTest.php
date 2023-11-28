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
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Handler\SubscriptionCreationHandler;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Subscription\Validator\SubscriptionSettingsValidator;
use Mollie\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SubscriptionCreationHandlerTest extends BaseTestCase
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var CreateSubscriptionDataFactory */
    private $createSubscriptionDataFactory;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;
    /** @var SubscriptionSettingsValidator */
    private $subscriptionSettingsValidator;
    /** @var CreateRecurringOrdersProductAction */
    private $createRecurringOrdersProductAction;
    /** @var CreateRecurringOrderAction */
    private $createRecurringOrderAction;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionApi = $this->mock(SubscriptionApi::class);
        $this->createSubscriptionDataFactory = $this->mock(CreateSubscriptionDataFactory::class);
        $this->subscriptionProductValidator = $this->mock(SubscriptionProductValidator::class);
        $this->subscriptionSettingsValidator = $this->mock(SubscriptionSettingsValidator::class);
        $this->createRecurringOrdersProductAction = $this->mock(CreateRecurringOrdersProductAction::class);
        $this->createRecurringOrderAction = $this->mock(CreateRecurringOrderAction::class);
    }

    public function testItSuccessfullyHandlesSubscriptionCreation(): void
    {
        $this->subscriptionSettingsValidator->expects($this->once())->method('validate');

        $this->subscriptionProductValidator->expects($this->exactly(2))->method('validate')->willReturnOnConsecutiveCalls(false, true);

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
            $this->subscriptionProductValidator,
            $this->subscriptionSettingsValidator,
            $this->createRecurringOrdersProductAction,
            $this->createRecurringOrderAction
        );

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

        $subscriptionCreationHandler->handle($order, 'test-method');
    }

    // TODO add the rest of the cases for exceptions
}
