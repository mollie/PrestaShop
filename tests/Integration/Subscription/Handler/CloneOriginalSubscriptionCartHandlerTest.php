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

namespace Mollie\Tests\Integration\Subscription\Handler;

use Mollie\Subscription\DTO\CloneOriginalSubscriptionCartData;
use Mollie\Subscription\Exception\CouldNotHandleOriginalSubscriptionCartCloning;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Handler\CloneOriginalSubscriptionCartHandler;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\AddressFactory;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\MolRecurringOrdersProductFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;

class CloneOriginalSubscriptionCartHandlerTest extends BaseTestCase
{
    public function testItSuccessfullyHandlesTask(): void
    {
        /** @var \Cart $originalCart */
        $originalCart = CartFactory::initialize()->create();

        $invoiceAddress = AddressFactory::create();
        $deliveryAddress = AddressFactory::create();

        $simpleProduct = ProductFactory::initialize()->create();
        $subscriptionProduct = ProductFactory::initialize()->create();

        $originalCart->updateQty(
            2,
            $simpleProduct->id,
            $simpleProduct->getDefaultIdProductAttribute()
        );

        $originalCart->updateQty(
            3,
            $subscriptionProduct->id,
            $subscriptionProduct->getDefaultIdProductAttribute()
        );

        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create([
            'id_product' => (int) $subscriptionProduct->id,
            'id_product_attribute' => $subscriptionProduct->getDefaultIdProductAttribute(),
            'unit_price' => 99.99,
        ]);

        /** @var CloneOriginalSubscriptionCartHandler $cloneOriginalSubscriptionCartHandler */
        $cloneOriginalSubscriptionCartHandler = $this->getService(CloneOriginalSubscriptionCartHandler::class);

        $result = $cloneOriginalSubscriptionCartHandler->run(CloneOriginalSubscriptionCartData::create(
            (int) $originalCart->id,
            (int) $recurringOrderProduct->id,
            (int) $invoiceAddress->id,
            (int) $deliveryAddress->id
        ));

        $newCartProducts = $result->getProducts(true);

        $this->assertCount(1, $newCartProducts);

        $this->assertEquals((int) $subscriptionProduct->id, (int) $newCartProducts[0]['id_product']);
        $this->assertEquals($subscriptionProduct->getDefaultIdProductAttribute(), $simpleProduct->getDefaultIdProductAttribute());
        $this->assertEquals(3, (int) $newCartProducts[0]['cart_quantity']);
        $this->assertEquals(99.99, (float) $newCartProducts[0]['price_with_reduction_without_tax']);

        $originalCart->delete();
        $result->delete();
        $invoiceAddress->delete();
        $deliveryAddress->delete();
        $simpleProduct->delete();
        $subscriptionProduct->delete();
        $recurringOrderProduct->delete();

        $this->removeFactories([
            $originalCart,
            $result,
            $deliveryAddress,
            $simpleProduct,
            $subscriptionProduct,
            $recurringOrderProduct,
        ]);
    }

    public function testItUnsuccessfullyHandlesTaskNoSubscriptionProductsAvailable(): void
    {
        /** @var \Cart $originalCart */
        $originalCart = CartFactory::initialize()->create();

        $simpleProduct = ProductFactory::initialize()->create();

        $originalCart->updateQty(
            2,
            $simpleProduct->id,
            $simpleProduct->getDefaultIdProductAttribute()
        );

        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create([
            'id_product' => 0,
            'id_product_attribute' => 0,
            'unit_price' => 99.99,
        ]);

        /** @var CloneOriginalSubscriptionCartHandler $cloneOriginalSubscriptionCartHandler */
        $cloneOriginalSubscriptionCartHandler = $this->getService(CloneOriginalSubscriptionCartHandler::class);

        $this->expectException(CouldNotHandleOriginalSubscriptionCartCloning::class);
        $this->expectExceptionCode(ExceptionCode::RECURRING_ORDER_SUBSCRIPTION_CART_SHOULD_HAVE_ONE_PRODUCT);

        $cloneOriginalSubscriptionCartHandler->run(CloneOriginalSubscriptionCartData::create(
            (int) $originalCart->id,
            (int) $recurringOrderProduct->id,
            0,
            0
        ));
    }


}
