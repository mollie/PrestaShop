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

namespace Mollie\Tests\Integration\Subscription\Action;

use Mollie\Subscription\Action\CreateSpecificPriceAction;
use Mollie\Subscription\DTO\CreateSpecificPriceData;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\ProductFactory;

class CreateSpecificPriceActionTest extends BaseTestCase
{
    public function testItSuccessfullyCreateSpecificPrice(): void
    {
        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();

        $this->assertDatabaseHasNot(\SpecificPrice::class, [
            'id_product' => (int) $product->id,
            'id_product_attribute' => (int) \Product::getDefaultAttribute($product->id),
            'price' => 10.00,
            'id_customer' => 1,
            'id_shop' => 1,
            'id_currency' => 1,
            'id_shop_group' => 1,
            'id_country' => 0,
            'id_group' => 0,
            'from_quantity' => 0,
            'reduction' => 0,
            'reduction_type' => 'amount',
            'from' => '0000-00-00 00:00:00',
            'to' => '0000-00-00 00:00:00',
        ]);

        /** @var CreateSpecificPriceAction $createSpecificPrice */
        $createSpecificPrice = $this->getService(CreateSpecificPriceAction::class);

        $result = $createSpecificPrice->run(CreateSpecificPriceData::create(
            (int) $product->id,
            (int) \Product::getDefaultAttribute($product->id),
            10.00,
            1,
            1,
            1,
            1
        ));

        $this->assertDatabaseHas(\SpecificPrice::class, [
            'id_product' => (int) $product->id,
            'id_product_attribute' => (int) \Product::getDefaultAttribute($product->id),
            'price' => 10.00,
            'id_customer' => 1,
            'id_shop' => 1,
            'id_currency' => 1,
            'id_shop_group' => 1,
            'id_country' => 0,
            'id_group' => 0,
            'from_quantity' => 0,
            'reduction' => 0,
            'reduction_type' => 'amount',
            'from' => '0000-00-00 00:00:00',
            'to' => '0000-00-00 00:00:00',
        ]);

        $product->delete();
        $result->delete();
    }
}
