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

use Mollie\Subscription\Action\UpdateRecurringOrderAction;
use Mollie\Subscription\DTO\UpdateRecurringOrderData;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\MolRecurringOrderFactory;

class UpdateRecurringOrderActionTest extends BaseTestCase
{
    public function testItSuccessfullyUpdatesRecord(): void
    {
        $data = [
            'id_mol_recurring_orders_product' => 1,
            'id_order' => 1,
            'id_cart' => 1,
            'id_currency' => 1,
            'id_customer' => 1,
            'id_address_delivery' => 1,
            'id_address_invoice' => 1,
            'mollie_subscription_id' => 'test-mollie-subscription-id',
            'mollie_customer_id' => 'test-mollie-customer-id',
            'description' => 'test-description',
            'status' => 'test-status',
            'total_tax_incl' => 19.99,
            'payment_method' => 'test-payment-method',
            'next_payment' => '2023-09-09 12:00:00',
            'reminder_at' => '2023-09-10 12:00:00',
            'cancelled_at' => '2023-09-11 12:00:00',
        ];

        $this->assertDatabaseHasNot(\MolRecurringOrder::class, $data);

        $recurringOrder = MolRecurringOrderFactory::create($data);

        $this->assertDatabaseHas(\MolRecurringOrder::class, array_merge($data, [
            'id_mol_recurring_order' => $recurringOrder->id,
        ]));

        /** @var UpdateRecurringOrderAction $updateRecurringOrderAction */
        $updateRecurringOrderAction = $this->getService(UpdateRecurringOrderAction::class);

        $updateRecurringOrderAction->run(UpdateRecurringOrderData::create(
            1,
            99.99
        ));

        $this->assertDatabaseHas(\MolRecurringOrder::class, array_merge($data, [
            'id_mol_recurring_order' => $recurringOrder->id,
            'total_tax_incl' => 99.99,
        ]));

        $recurringOrder->delete();
    }
}
