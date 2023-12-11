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

namespace Mollie\Tests\Integration\Factory;

class MolRecurringOrderFactory implements FactoryInterface
{
    public static function create(array $data = []): \MolRecurringOrder
    {
        $recurringOrder = new \MolRecurringOrder();

        $recurringOrder->id_mol_recurring_orders_product = $data['id_mol_recurring_orders_product'] ?? 1;
        $recurringOrder->id_order = $data['id_order'] ?? 1;
        $recurringOrder->id_cart = $data['id_cart'] ?? 1;
        $recurringOrder->id_currency = $data['id_currency'] ?? 1;
        $recurringOrder->id_customer = $data['id_currency'] ?? 1;
        $recurringOrder->id_address_delivery = $data['id_address_delivery'] ?? 1;
        $recurringOrder->id_address_invoice = $data['id_address_invoice'] ?? 1;
        $recurringOrder->description = $data['description'] ?? 'test-description';
        $recurringOrder->status = $data['status'] ?? 'test-status';
        $recurringOrder->total_tax_incl = $data['total_tax_incl'] ?? 10.00;
        $recurringOrder->payment_method = $data['payment_method'] ?? 'test-payment-method';
        $recurringOrder->next_payment = $data['next_payment'] ?? '1990-01-01 12:00:00';
        $recurringOrder->reminder_at = $data['reminder_at'] ?? '1990-01-01 12:00:00';
        $recurringOrder->cancelled_at = $data['cancelled_at'] ?? '0000-00-00 00:00:00';
        $recurringOrder->mollie_subscription_id = $data['mollie_subscription_id'] ?? 'test-mollie-subscription-id';
        $recurringOrder->mollie_customer_id = $data['mollie_customer_id'] ?? 'test-mollie-customer-id';
        $recurringOrder->date_update = $data['date_update'] ?? '';

        $recurringOrder->add();

        return $recurringOrder;
    }
}
