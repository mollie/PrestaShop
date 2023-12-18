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

class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'iso_code' => $this->faker->currencyCode,
            'precision' => $this->faker->numberBetween(2, 6),
            'conversion_rate' => $this->faker->randomFloat(2, 0.5, 1.5),
            'active' => true,
            'deleted' => false,
        ];
    }
}
