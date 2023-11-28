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

use Mollie\Subscription\Action\CreateRecurringOrdersProductAction;
use Mollie\Subscription\DTO\CreateRecurringOrdersProductData;
use Mollie\Tests\Integration\BaseTestCase;

class CreateRecurringOrdersProductActionTest extends BaseTestCase
{
    public function testItSuccessfullyCreateDatabaseEntry(): void
    {
        $this->assertDatabaseHasNot(\MolRecurringOrdersProduct::class, [
            'id_product' => 1,
            'id_product_attribute' => 1,
            'quantity' => 1,
            'unit_price' => 19.99,
        ]);

        /** @var CreateRecurringOrdersProductAction $createRecurringOrdersProductAction */
        $createRecurringOrdersProductAction = $this->getService(CreateRecurringOrdersProductAction::class);

        $result = $createRecurringOrdersProductAction->run(CreateRecurringOrdersProductData::create(
            1,
            1,
            1,
            19.99
        ));

        $this->assertDatabaseHas(\MolRecurringOrdersProduct::class, [
            'id_product' => 1,
            'id_product_attribute' => 1,
            'quantity' => 1,
            'unit_price' => 19.99,
        ]);

        $result->delete();
    }
}
