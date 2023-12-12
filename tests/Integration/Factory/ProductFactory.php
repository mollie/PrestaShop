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

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $name = $this->faker->text(10);

        return [
            'id_tax_rules_group' => $this->faker->numberBetween(1, 99), // TODO tax rules group factory
            'name' => $name,
            'description_short' => $this->faker->text(50),
            'price' => $this->faker->randomFloat(6, 1000, 10000),
            'link_rewrite' => \Tools::link_rewrite($name),
            'out_of_stock' => 1,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (\Product $product) {
            \Product::flushPriceCache();

            \StockAvailable::setQuantity(
                (int) $product->id,
                0,
                10
            );
        });
    }
}
