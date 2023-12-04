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

use Mollie\Subscription\Action\CreateRecurringOrderAction;
use Mollie\Subscription\DTO\CreateRecurringOrderData;
use Mollie\Tests\Integration\BaseTestCase;

class CreateRecurringOrderActionTest extends BaseTestCase
{
    public function testItSuccessfullyCreateDatabaseEntry(): void
    {
        $this->assertDatabaseHasNot(\MolRecurringOrder::class, [
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
        ]);

        /** @var CreateRecurringOrderAction $createRecurringOrderAction */
        $createRecurringOrderAction = $this->getService(CreateRecurringOrderAction::class);

        $result = $createRecurringOrderAction->run(CreateRecurringOrderData::create(
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            'test-description',
            'test-status',
            19.99,
            'test-payment-method',
            '2023-09-09 12:00:00',
            '2023-09-10 12:00:00',
            '2023-09-11 12:00:00',
            'test-mollie-subscription-id',
            'test-mollie-customer-id'
        ));

        $this->assertDatabaseHas(\MolRecurringOrder::class, [
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
        ]);

        $result->delete();
    }
}
