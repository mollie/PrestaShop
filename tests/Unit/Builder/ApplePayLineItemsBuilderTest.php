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

namespace Builder;

use Cart;
use Mollie;
use Mollie\Builder\ApplePayDirect\ApplePayLineItemsBuilder;
use Mollie\Factory\ModuleFactory;
use PHPUnit\Framework\TestCase;

class ApplePayLineItemsBuilderTest extends TestCase
{
    /**
     * @dataProvider cartTotalsProvider
     */
    public function testBuildSplitsTheTotalIntoLinesThatAddUp(
        float $productsTaxExcl,
        float $shipping,
        float $discounts,
        float $orderTotal,
        float $paymentFee,
        array $expected
    ): void {
        $module = $this->createMock(Mollie::class);
        $module->method('l')->willReturnArgument(0);

        $moduleFactory = $this->createMock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn($module);

        $cart = $this->createMock(Cart::class);
        $cart->method('getOrderTotal')->willReturnCallback(
            function ($withTaxes, $type) use ($productsTaxExcl, $shipping, $discounts) {
                $totals = [
                    Cart::ONLY_PRODUCTS => $productsTaxExcl,
                    Cart::ONLY_SHIPPING => $shipping,
                    Cart::ONLY_DISCOUNTS => $discounts,
                ];

                return $totals[$type];
            }
        );

        $lineItems = (new ApplePayLineItemsBuilder($moduleFactory))->build($cart, $orderTotal, $paymentFee);

        $this->assertSame($expected, array_map(function (array $line) {
            return [$line['label'], $line['amount']];
        }, $lineItems));

        $this->assertEqualsWithDelta(
            $orderTotal + $paymentFee,
            array_sum(array_column($lineItems, 'amount')),
            0.001
        );
    }

    public function cartTotalsProvider(): array
    {
        return [
            '20% VAT with taxed product and untaxed shipping' => [
                68.33, 18.15, 0.0, 100.15, 0.0,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['VAT', '13.67'],
                    ['Shipping', '18.15'],
                ],
            ],
            '0% VAT country' => [
                68.33, 18.15, 0.0, 86.48, 0.0,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['VAT', '0.00'],
                    ['Shipping', '18.15'],
                ],
            ],
            'voucher and payment fee' => [
                68.33, 18.15, 5.0, 95.15, 1.5,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['Discount', '-5.00'],
                    ['VAT', '13.67'],
                    ['Shipping', '18.15'],
                    ['Payment fee', '1.50'],
                ],
            ],
        ];
    }
}
