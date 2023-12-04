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

use Mollie\Subscription\DTO\UpdateSubscriptionCarrierData;
use Mollie\Subscription\Exception\CouldNotProvideUpdateSubscriptionCarrierData;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Provider\SubscriptionCarrierDeliveryPriceProvider;
use Mollie\Subscription\Provider\SubscriptionProductProvider;
use Mollie\Subscription\Provider\UpdateSubscriptionCarrierDataProvider;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\SecureKeyUtility;

class UpdateSubscriptionCarrierDataProviderTest extends BaseTestCase
{
    /** @var SubscriptionCarrierDeliveryPriceProvider */
    private $subscriptionCarrierDeliveryPriceProvider;
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionCarrierDeliveryPriceProvider = $this->mock(SubscriptionCarrierDeliveryPriceProvider::class);
        $this->subscriptionProductProvider = $this->mock(SubscriptionProductProvider::class);
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

        $this->subscriptionCarrierDeliveryPriceProvider->expects($this->once())->method('getPrice')->willReturn(12.34);

        $currency = $this->mock(\Currency::class);

        $currency->iso_code = 'EUR';

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn($currency);

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->currencyRepository,
            $this->moduleFactory,
            $this->subscriptionCarrierDeliveryPriceProvider,
            $this->subscriptionProductProvider
        );

        $result = $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierData::create(
            1,
            2,
            3,
            4
        ));

        $this->assertEquals(1, $result->getCustomerId());
        $this->assertEquals(2, $result->getSubscriptionId());
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

        $this->subscriptionCarrierDeliveryPriceProvider->expects($this->never())->method('getPrice');

        $this->currencyRepository->expects($this->never())->method('findOneBy');

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->currencyRepository,
            $this->moduleFactory,
            $this->subscriptionCarrierDeliveryPriceProvider,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotProvideUpdateSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_ORDER);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierData::create(
            1,
            2,
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

        $this->subscriptionCarrierDeliveryPriceProvider->expects($this->never())->method('getPrice');

        $this->currencyRepository->expects($this->never())->method('findOneBy');

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->currencyRepository,
            $this->moduleFactory,
            $this->subscriptionCarrierDeliveryPriceProvider,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotProvideUpdateSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_SUBSCRIPTION_PRODUCT);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierData::create(
            1,
            2,
            3,
            4
        ));
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindProvideCarrierDeliveryPrice(): void
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

        $this->subscriptionCarrierDeliveryPriceProvider->expects($this->once())->method('getPrice')->willThrowException(new \Exception('', 0));

        $this->currencyRepository->expects($this->never())->method('findOneBy');

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->currencyRepository,
            $this->moduleFactory,
            $this->subscriptionCarrierDeliveryPriceProvider,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotProvideUpdateSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_PROVIDE_CARRIER_DELIVERY_PRICE);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierData::create(
            1,
            2,
            3,
            4
        ));
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindCurrency(): void
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

        $this->subscriptionCarrierDeliveryPriceProvider->expects($this->once())->method('getPrice')->willReturn(12.34);

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $updateSubscriptionCarrierDataProvider = new UpdateSubscriptionCarrierDataProvider(
            $this->orderRepository,
            $this->currencyRepository,
            $this->moduleFactory,
            $this->subscriptionCarrierDeliveryPriceProvider,
            $this->subscriptionProductProvider
        );

        $this->expectException(CouldNotProvideUpdateSubscriptionCarrierData::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_FIND_CURRENCY);

        $updateSubscriptionCarrierDataProvider->get(UpdateSubscriptionCarrierData::create(
            1,
            2,
            3,
            4
        ));
    }
}
