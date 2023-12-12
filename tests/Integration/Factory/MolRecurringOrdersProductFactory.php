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

class MolRecurringOrdersProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \MolRecurringOrdersProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_product' => function () {
                return ProductFactory::initialize()->create()->id;
            },
            'id_product_attribute' => 0, // TODO product factory with combinations
            'quantity' => $this->faker->numberBetween(1, 9),
            'unit_price' => function ($attributes) {
                return $attributes['price'];
            },
            'date_update' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
