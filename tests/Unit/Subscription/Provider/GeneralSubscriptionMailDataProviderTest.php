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

namespace Mollie\Tests\Unit\Subscription\Provider;

use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Repository\ProductRepositoryInterface;
use Mollie\Shared\Core\Shared\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\Provider\GeneralSubscriptionMailDataProvider;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Tests\Unit\BaseTestCase;

class GeneralSubscriptionMailDataProviderTest extends BaseTestCase
{
    /**
     * @dataProvider formattedPriceProvider
     */
    public function testItKeepsLocaleFormattedPrices(string $isoCode, string $formattedUnitPrice, string $formattedTotalPrice): void
    {
        $recurringOrder = $this->mock(\MolRecurringOrder::class);
        $recurringOrder->mollie_subscription_id = 'sub_test';
        $recurringOrder->id_mol_recurring_orders_product = 2;
        $recurringOrder->id_customer = 3;
        $recurringOrder->id_currency = 4;
        $recurringOrder->total_tax_incl = 2469.12;

        $recurringOrderProduct = $this->mock(\MolRecurringOrdersProduct::class);
        $recurringOrderProduct->id_product = 5;
        $recurringOrderProduct->unit_price = 1234.56;
        $recurringOrderProduct->quantity = 2;

        $customer = $this->mock(\Customer::class);
        $customer->firstname = 'Jane';
        $customer->lastname = 'Customer';
        $customer->email = 'jane.customer@example.test';
        $customer->id_lang = 1;
        $customer->id_shop = 1;

        $product = $this->mock(\Product::class);
        $product->name = 'Test product';

        $currency = $this->mock(\Currency::class);
        $currency->iso_code = $isoCode;

        $recurringOrderRepository = $this->mock(RecurringOrderRepositoryInterface::class);
        $recurringOrderRepository->method('findOrFail')->willReturn($recurringOrder);

        $recurringOrdersProductRepository = $this->mock(RecurringOrdersProductRepositoryInterface::class);
        $recurringOrdersProductRepository->method('findOrFail')->willReturn($recurringOrderProduct);

        $customerRepository = $this->mock(CustomerRepositoryInterface::class);
        $customerRepository->method('findOrFail')->willReturn($customer);

        $productRepository = $this->mock(ProductRepositoryInterface::class);
        $productRepository->method('findOrFail')->willReturn($product);

        $currencyRepository = $this->mock(CurrencyRepositoryInterface::class);
        $currencyRepository->method('findOrFail')->willReturn($currency);

        $this->context->method('formatPrice')->willReturnMap([
            [1234.56, $isoCode, $formattedUnitPrice],
            [2469.12, $isoCode, $formattedTotalPrice],
        ]);

        $provider = new GeneralSubscriptionMailDataProvider(
            $recurringOrderRepository,
            $recurringOrdersProductRepository,
            $customerRepository,
            $productRepository,
            $this->context,
            $currencyRepository
        );

        $result = $provider->run(1)->toArray();

        $this->assertSame($formattedUnitPrice, $result['unit_price']);
        $this->assertSame($formattedTotalPrice, $result['total_price']);
    }

    public function formattedPriceProvider(): array
    {
        return [
            'en_US' => ['USD', '$1,234.56', '$2,469.12'],
            'de_DE' => ['EUR', '1.234,56 €', '2.469,12 €'],
            'fr_FR' => ['EUR', '1 234,56 €', '2 469,12 €'],
        ];
    }
}
