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

class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_currency' => \Configuration::get('PS_CURRENCY_DEFAULT'),
            'id_carrier' => function () {
                return CarrierFactory::create()->id;
            },
            'id_address_delivery' => function () {
                return AddressFactory::create()->id;
            },
            'id_address_invoice' => function ($attributes) {
                return $attributes['id_address_delivery'];
            },
            'id_customer' => function () {
                return CustomerFactory::create()->id;
            },
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (\Cart $cart) {
            \Context::getContext()->cart = $cart;
        });
    }
}
