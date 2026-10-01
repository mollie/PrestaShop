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
        array $cartTotals,
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
            function ($withTaxes, $type) use ($cartTotals) {
                $key = $type . ($withTaxes ? '_incl' : '_excl');
                $this->assertArrayHasKey($key, $cartTotals, "Unexpected getOrderTotal() call: $key");

                return $cartTotals[$key];
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
                $this->cartTotals(68.33, 18.15, 0.0, 0.0), 100.15, 0.0,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['VAT', '13.67'],
                    ['Shipping', '18.15'],
                ],
            ],
            '0% VAT country' => [
                $this->cartTotals(68.33, 18.15, 0.0, 0.0), 86.48, 0.0,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['VAT', '0.00'],
                    ['Shipping', '18.15'],
                ],
            ],
            // 82.00 incl - 5.00 incl voucher (4.17 excl) + 18.15 shipping; real VAT is 13.67 - 0.83
            'voucher and payment fee' => [
                $this->cartTotals(68.33, 18.15, 4.17, 0.0), 95.15, 1.5,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['Discount', '-4.17'],
                    ['VAT', '12.84'],
                    ['Shipping', '18.15'],
                    ['Payment fee', '1.50'],
                ],
            ],
            // 82.00 incl + 2.40 wrapping incl + 18.15 shipping
            'gift wrapping' => [
                $this->cartTotals(68.33, 18.15, 0.0, 2.40), 102.55, 0.0,
                [
                    ['Products (tax excl.)', '68.33'],
                    ['VAT', '13.67'],
                    ['Shipping', '18.15'],
                    ['Gift wrapping', '2.40'],
                ],
            ],
        ];
    }

    private function cartTotals(float $productsTaxExcl, float $shipping, float $discountsTaxExcl, float $wrapping): array
    {
        return [
            Cart::ONLY_PRODUCTS . '_excl' => $productsTaxExcl,
            Cart::ONLY_SHIPPING . '_incl' => $shipping,
            Cart::ONLY_DISCOUNTS . '_excl' => $discountsTaxExcl,
            Cart::ONLY_WRAPPING . '_incl' => $wrapping,
        ];
    }
}
