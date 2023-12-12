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

use Invertus\Prestashop\Models\Factory\Factory;

class MolRecurringOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \MolRecurringOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_mol_recurring_orders_product' => $this->faker->numberBetween(1, 99999), // TODO recurring order product factory
            'id_order' => $this->faker->numberBetween(1, 99999), // TODO order factory
            'id_currency' => $this->faker->numberBetween(1, 99999), // TODO currency factory
            'id_customer' => $this->faker->numberBetween(1, 99999), // TODO customer factory
            'id_address_delivery' => $this->faker->numberBetween(1, 99999), // TODO address factory
            'id_address_invoice' => $this->faker->numberBetween(1, 99999), // TODO address factory
            'description' => $this->faker->text(20),
            'status' => $this->faker->text(20),
            'total_tax_incl' => $this->faker->numberBetween(10, 100),
            'payment_method' => $this->faker->text(20),
            'next_payment' => $this->faker->date('Y-m-d H:i:s', '+3 weeks'),
            'reminder_at' => $this->faker->date('Y-m-d H:i:s', '+3 weeks'),
            'cancelled_at' => $this->faker->date('Y-m-d H:i:s', '+10 weeks'),
            'mollie_subscription_id' => $this->faker->text(20),
            'mollie_customer_id' => $this->faker->text(20),
            'date_update' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
