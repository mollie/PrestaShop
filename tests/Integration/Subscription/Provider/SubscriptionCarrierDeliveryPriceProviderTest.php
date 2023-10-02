<?php

namespace Mollie\Tests\Integration\Subscription\Provider;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierDeliveryPrice;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Provider\SubscriptionCarrierDeliveryPriceProvider;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\AddressFactory;
use Mollie\Tests\Integration\Factory\CarrierFactory;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;

class SubscriptionCarrierDeliveryPriceProviderTest extends BaseTestCase
{
    public function setUp(): void
    {
        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->getService(ConfigurationAdapter::class);

        $this->subscriptionOrderCarrierId = $configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        parent::setUp();
    }

    public function tearDown(): void
    {
        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->getService(ConfigurationAdapter::class);

        $configuration->updateValue(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID, $this->subscriptionOrderCarrierId);

        parent::tearDown();
    }

    public function testItSuccessfullyProvidesCarrierDeliveryPrice(): void
    {
        $address = AddressFactory::create();
        $carrier = CarrierFactory::create([
            'price' => 999.00,
        ]);
        $cart = CartFactory::create([
            'id_carrier' => $carrier->id,
        ]);

        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->getService(ConfigurationAdapter::class);

        $configuration->updateValue(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID, $carrier->id);

        $targetProduct = ProductFactory::create([
            'quantity' => 10,
        ]);
        $product1 = ProductFactory::create([
            'quantity' => 10,
        ]);
        $product2 = ProductFactory::create([
            'quantity' => 10,
        ]);

        $cart->updateQty(2, $targetProduct->id);

        $targetProductArray = $cart->getProducts()[0];

        $cart->updateQty(2, $product1->id);
        $cart->updateQty(3, $product2->id);

        /** @var SubscriptionCarrierDeliveryPriceProvider $subscriptionCarrierDeliveryPriceProvider */
        $subscriptionCarrierDeliveryPriceProvider = $this->getService(SubscriptionCarrierDeliveryPriceProvider::class);

        $result = $subscriptionCarrierDeliveryPriceProvider->getPrice(
            $address->id,
            $cart->id,
            $cart->id_customer,
            $targetProductArray
        );

        $this->assertEquals(999.00, $result);

        $this->removeFactories([
            $carrier,
            $address,
            $cart,
            $targetProduct,
            $product1,
            $product2,
        ]);
    }

    public function testItUnsuccessfullyProvidesCarrierDeliveryPriceCarrierIsOutOfZone(): void
    {
        $address = AddressFactory::create();
        $carrier = CarrierFactory::create([
            'price' => 999.00,
            'id_zones_to_delete' => [
                $address::getZoneById($address->id),
            ],
        ]);
        $cart = CartFactory::create([
            'id_carrier' => $carrier->id,
        ]);

        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->getService(ConfigurationAdapter::class);

        $configuration->updateValue(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID, $carrier->id);

        $targetProduct = ProductFactory::create([
            'quantity' => 10,
        ]);
        $product1 = ProductFactory::create([
            'quantity' => 10,
        ]);
        $product2 = ProductFactory::create([
            'quantity' => 10,
        ]);

        $cart->updateQty(2, $targetProduct->id);

        $targetProductArray = $cart->getProducts()[0];

        $cart->updateQty(2, $product1->id);
        $cart->updateQty(3, $product2->id);

        $this->expectException(CouldNotProvideSubscriptionCarrierDeliveryPrice::class);
        $this->expectExceptionCode(ExceptionCode::ORDER_FAILED_TO_APPLY_SELECTED_CARRIER);

        /** @var SubscriptionCarrierDeliveryPriceProvider $subscriptionCarrierDeliveryPriceProvider */
        $subscriptionCarrierDeliveryPriceProvider = $this->getService(SubscriptionCarrierDeliveryPriceProvider::class);

        $subscriptionCarrierDeliveryPriceProvider->getPrice(
            $address->id,
            $cart->id,
            $cart->id_customer,
            $targetProductArray
        );

        $this->removeFactories([
            $carrier,
            $address,
            $cart,
            $targetProduct,
            $product1,
            $product2,
        ]);
    }

    /**
     * @param \ObjectModel[] $objects
     */
    private function removeFactories(array $objects): void
    {
        foreach ($objects as $object) {
            $object->delete();
        }
    }
}
