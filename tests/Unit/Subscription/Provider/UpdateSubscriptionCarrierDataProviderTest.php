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

namespace Mollie\Tests\Unit\Subscription\Provider;

use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\UpdateSubscriptionCarrierProviderData;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierData;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Subscription\Provider\SubscriptionProductProvider;
use Mollie\Subscription\Provider\UpdateSubscriptionCarrierDataProvider;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\SecureKeyUtility;

class UpdateSubscriptionCarrierDataProviderTest extends BaseTestCase
{
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionProductProvider = $this->mock(SubscriptionProductProvider::class);
        $this->subscriptionOrderAmountProvider = $this->mock(SubscriptionOrderAmountProvider::class);
    }

    public function testItSuccessfullyProvidesData(): void
    {
        $this->module->name = 'mollie';

        $this->moduleFactory->expects($this->once())->method('getModule')->willReturn($this->module);

        $order = $this->mock(\Order::class);

        $order->id_customer = 1;
        $order->id_cart = 1;
        $order->id_address_delivery = 1;
        $order->id_currency = 1;

        $order->expects($this->once())->method('getCartProducts')->willReturn([]);

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn(['total_price_tax_incl' => 10.00]);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willReturn(new Amount(22.34, 'EUR'));

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->moduleFactory,
            $this->subscriptionProductProvider,
            $this->subscriptionOrderAmountProvider
        );

        $result = $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierProviderData::create(
            'test-mollie-customer-id',
            'test-mollie-subscription-id',
            3,
            4
        ));

        $this->assertEquals('test-mollie-customer-id', $result->getCustomerId());
        $this->assertEquals('test-mollie-subscription-id', $result->getSubscriptionId());
        $this->assertEquals([
            'metadata' => [
                'secure_key' => SecureKeyUtility::generateReturnKey(
                    (int) $order->id_customer,
                    (int) $order->id_cart,
                    $this->module->name
                ),
                'subscription_carrier_id' => 3,
            ],
            'amount' => [
                'value' => 22.34,
                'currency' => 'EUR',
            ],
        ], $result->toArray());
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindOrder(): void
    {
        $this->module->name = 'mollie';

        $this->moduleFactory->expects($this->once())->method('getModule')->willReturn($this->module);

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $this->subscriptionProductProvider->expects($this->never())->method('getProduct');

        $this->subscriptionOrderAmountProvider->expects($this->never())->method('get');

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->moduleFactory,
            $this->subscriptionProductProvider,
            $this->subscriptionOrderAmountProvider
        );

        $this->expectException(CouldNotProvideSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_ORDER);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierProviderData::create(
            'test-mollie-customer-id',
            'test-mollie-subscription-id',
            3,
            4
        ));
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindSubscriptionProduct(): void
    {
        $this->module->name = 'mollie';

        $this->moduleFactory->expects($this->once())->method('getModule')->willReturn($this->module);

        $order = $this->mock(\Order::class);

        $order->id_customer = 1;
        $order->id_cart = 1;
        $order->id_address_delivery = 1;
        $order->id_currency = 1;

        $order->expects($this->once())->method('getCartProducts')->willReturn([]);

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn([]);

        $this->subscriptionOrderAmountProvider->expects($this->never())->method('get');

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->moduleFactory,
            $this->subscriptionProductProvider,
            $this->subscriptionOrderAmountProvider
        );

        $this->expectException(CouldNotProvideSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierProviderData::create(
            'test-mollie-customer-id',
            'test-mollie-subscription-id',
            3,
            4
        ));
    }

    public function testItUnsuccessfullyProvidesDataFailedToProvideSubscriptionOrderAmount(): void
    {
        $this->module->name = 'mollie';

        $this->moduleFactory->expects($this->once())->method('getModule')->willReturn($this->module);

        $order = $this->mock(\Order::class);

        $order->id_customer = 1;
        $order->id_cart = 1;
        $order->id_address_delivery = 1;
        $order->id_currency = 1;

        $order->expects($this->once())->method('getCartProducts')->willReturn([]);

        $this->orderRepository->expects($this->once())->method('findOneBy')->willReturn($order);

        $this->subscriptionProductProvider->expects($this->once())->method('getProduct')->willReturn(['total_price_tax_incl' => 10.00]);

        $this->subscriptionOrderAmountProvider->expects($this->once())->method('get')->willThrowException(new \Exception('', 0));

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->moduleFactory,
            $this->subscriptionProductProvider,
            $this->subscriptionOrderAmountProvider
        );

        $this->expectException(CouldNotProvideSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_PROVIDE_SUBSCRIPTION_ORDER_AMOUNT);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierProviderData::create(
            'test-mollie-customer-id',
            'test-mollie-subscription-id',
            3,
            4
        ));
    }
}
