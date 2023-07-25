<?php

namespace Mollie\Tests\Integration\src\Action;

use Mollie\Action\UpdateOrderTotalsWithPaymentFeeAction;
use Mollie\DTO\UpdateOrderTotalsWithPaymentFeeData;
use Mollie\Tests\Integration\BaseTestCase;

class UpdateOrderTotalsWithPaymentFeeActionTest extends BaseTestCase
{
    public function testItSuccessfullyUpdatesOrderTotal(): void
    {
        /** @var UpdateOrderTotalsWithPaymentFeeAction $updateOrderTotalsWithPaymentFeeAction */
        $updateOrderTotalsWithPaymentFeeAction = $this->getService(UpdateOrderTotalsWithPaymentFeeAction::class);

        $this->assertDatabaseHasNot(\Order::class,
            [
                'id_order' => 1,
                'total_paid_tax_excl' => 18.26,
                'total_paid_tax_incl' => 22.1,
                'total_paid' => 22.1,
                'total_paid_real' => 22.1,
            ]
        );

        $updateOrderTotalsWithPaymentFeeAction->run(UpdateOrderTotalsWithPaymentFeeData::create(
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
                'total_paid_real' => 22.1,
            ]
        );
    }
}
