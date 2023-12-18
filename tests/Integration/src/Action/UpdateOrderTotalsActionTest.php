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

namespace Mollie\Tests\Integration\src\Action;

use Mollie\Action\UpdateOrderTotalsAction;
use Mollie\DTO\UpdateOrderTotalsData;
use Mollie\Tests\Integration\BaseTestCase;

class UpdateOrderTotalsActionTest extends BaseTestCase
{
    public function testItSuccessfullyUpdatesOrderTotals(): void
    {
        // TODO order factory
        $originalOrder = new \Order(1);

        /** @var UpdateOrderTotalsAction $updateOrderTotalsAction */
        $updateOrderTotalsAction = $this->getService(UpdateOrderTotalsAction::class);

        $this->assertDatabaseHasNot(\Order::class,
            [
                'id_order' => 1,
                'total_paid_tax_excl' => 18.26,
                'total_paid_tax_incl' => 22.1,
                'total_paid' => 22.1,
            ]
        );

        $updateOrderTotalsAction->run(UpdateOrderTotalsData::create(
            1,
            12.1,
            10,
            22.1,
            10,
            8.264462
        ));

        $this->assertDatabaseHas(\Order::class,
            [
                'id_order' => 1,
                'total_paid_tax_excl' => 18.26,
                'total_paid_tax_incl' => 22.1,
                'total_paid' => 22.1,
            ]
        );

        $originalOrder->save();
    }
}
