<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Cart;
use Configuration;
use Currency;
use Mollie;
use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
use Mollie\Utility\CalculationUtility;
use Mollie\Utility\CartPriceUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextFormatUtility;
use Tools;

class CartLinesService
{

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * @param float $amount
     *
     * @param $paymentFee
     * @param Cart $cart
     * @return array
     */
    public function getCartLines($amount, $paymentFee, Cart $cart)
    {
        $oCurrency = new Currency($cart->id_currency);
        $apiRoundingPrecision = Mollie\Config\Config::API_ROUNDING_PRECISION;
        $vatRatePrecision = Mollie\Config\Config::VAT_RATE_ROUNDING_PRECISION;

        $totalPrice = round($amount, $apiRoundingPrecision);
        $cartSummary = $cart->getSummaryDetails();
        $shipping = round($cart->getTotalShippingCost(null, true), $apiRoundingPrecision);
        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $shipping = 0;
            }
        }

        $cartItems = $cart->getProducts();
        $wrapping = Configuration::get('PS_GIFT_WRAPPING') ? round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
//        $totalDiscounts = isset($cartSummary['total_discounts']) ? round($cartSummary['total_discounts'], $apiRoundingPrecision) : 0;
        $totalDiscounts = 0;
        $remaining = round(
            CalculationUtility::getCartRemainingPrice($totalPrice, $shipping, $wrapping),
            $apiRoundingPrecision
        );

        $aItems = [];
        /* Item */
        foreach ($cartItems as $cartItem) {
            // Get the rounded total w/ tax
            $roundedTotalWithTax = round($cartItem['total_wt'], $apiRoundingPrecision);

            // Skip if no qty
            $quantity = (int)$cartItem['cart_quantity'];
            if ($quantity <= 0 || $cartItem['price_wt'] <= 0) {
                continue;
            }

            // Generate the product hash
            $idProduct = number_format($cartItem['id_product']);
            $idProductAttribute = number_format($cartItem['id_product_attribute']);
            $idCustomization = number_format($cartItem['id_customization']);

            $productHash = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}";
            $aItems[$productHash] = [];

            foreach ($cartSummary['gift_products'] as $gift_product) {
                if ($gift_product['id_product'] === $cartItem['id_product']) {
                    $roundedTotalWithTax = NumberUtility::minus(
                        $roundedTotalWithTax,
                        NumberUtility::times($gift_product['cart_quantity'], $gift_product['price_with_reduction'])
                    );
                    $quantity = NumberUtility::minus($quantity, $gift_product['cart_quantity']);

                    $productHashGift = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}gift";
                    $aItems[$productHashGift][] = [
                        'name' => $cartItem['name'],
                        'sku' => $productHashGift,
                        'targetVat' => (float)$cartItem['rate'],
                        'quantity' => $gift_product['cart_quantity'],
                        'unitPrice' => 0,
                        'totalAmount' => 0,
                    ];
                    break;
                }
            }

            // Try to spread this product evenly and account for rounding differences on the order line
            foreach (CartPriceUtility::spreadAmountEvenly($roundedTotalWithTax, $quantity) as $unitPrice => $qty) {
                $aItems[$productHash][] = [
                    'name' => $cartItem['name'],
                    'sku' => $productHash,
                    'targetVat' => (float)$cartItem['rate'],
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => (float)NumberUtility::times($unitPrice, $qty),
                ];
                $remaining -= round((float)NumberUtility::times($unitPrice, $qty), $apiRoundingPrecision);
            }
        }

        // Add discount if applicable
        if ($totalDiscounts >= 0.01) {
            $aItems['discount'] = [
                [
                    'name' => 'Discount',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => -round($totalDiscounts, $apiRoundingPrecision),
                    'totalAmount' => -round($totalDiscounts, $apiRoundingPrecision),
                    'targetVat' => 0,
                ],
            ];
            $remaining -= $totalDiscounts;
        }

        // Compensate for order total rounding inaccuracies
        $remaining = round($remaining, $apiRoundingPrecision);
        if ($remaining < 0) {
            foreach (array_reverse($aItems) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));

                // Remove when total is lower than remaining
                if ($totalAmount <= $remaining) {
                    // The line total is less than remaining, we should remove this line group and continue
                    $remaining = $remaining - $totalAmount;
                    unset($items);
                    continue;
                }

                // Otherwise spread the cart line again with the updated total
                $aItems[$hash] = static::spreadCartLineGroup($items, $totalAmount - $remaining);
                break;
            }
        } elseif ($remaining > 0) {
            foreach (array_reverse($aItems) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));
                // Otherwise spread the cart line again with the updated total
                $aItems[$hash] = static::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        }

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        foreach ($aItems as $productHash => $aItem) {
            $aItems[$productHash] = array_map(function ($line) use ($apiRoundingPrecision, $vatRatePrecision) {
                $quantity = (int)$line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = round(CalculationUtility::getUnitPriceNoTax(
                    $line['unitPrice'],
                    $targetVat
                ),
                    $apiRoundingPrecision
                );

                // Calculate VAT
                $totalAmount = round(NumberUtility::times($unitPrice, $quantity), $apiRoundingPrecision);
                $actualVatRate = 0;//
                if ($unitPriceNoTax > 0) {
                    $actualVatRate = round(
                        $vatAmount = CalculationUtility::getActualVatRate($unitPrice, $unitPriceNoTax, $quantity),
                        $vatRatePrecision
                    );
                }
                $vatRateWithPercentages = NumberUtility::plus($actualVatRate, 100);
                $vatAmount = NumberUtility::times(
                    $totalAmount,
                    NumberUtility::divide($actualVatRate, $vatRateWithPercentages)
                );

                $newItem = [
                    'name' => $line['name'],
                    'quantity' => (int)$quantity,
                    'unitPrice' => round($unitPrice, $apiRoundingPrecision),
                    'totalAmount' => round($totalAmount, $apiRoundingPrecision),
                    'vatRate' => round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount' => round($vatAmount, $apiRoundingPrecision),
                ];
                if (isset($line['sku'])) {
                    $newItem['sku'] = $line['sku'];
                }

                return $newItem;
            }, $aItem);
        }

        // Add shipping
        if (round($shipping, 2) > 0) {
            $shippingVatRate = round(($cartSummary['total_shipping'] - $cartSummary['total_shipping_tax_exc']) / $cartSummary['total_shipping_tax_exc'] * 100, $apiRoundingPrecision);

            $aItems['shipping'] = [
                [
                    'name' => $this->module->l('Shipping'),
                    'quantity' => 1,
                    'unitPrice' => round($shipping, $apiRoundingPrecision),
                    'totalAmount' => round($shipping, $apiRoundingPrecision),
                    'vatAmount' => round($shipping * $shippingVatRate / ($shippingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $shippingVatRate,
                ],
            ];
        }

        // Add wrapping
        if (round($wrapping, 2) > 0) {
            $wrappingVatRate = round(
                CalculationUtility::getActualVatRate(
                    $cartSummary['total_wrapping'],
                    $cartSummary['total_wrapping_tax_exc']
                ),
                $vatRatePrecision
            );

            $aItems['wrapping'] = [
                [
                    'name' => $this->module->l('Gift wrapping'),
                    'quantity' => 1,
                    'unitPrice' => round($wrapping, $apiRoundingPrecision),
                    'totalAmount' => round($wrapping, $apiRoundingPrecision),
                    'vatAmount' => round($wrapping * $wrappingVatRate / ($wrappingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $wrappingVatRate,
                ],
            ];
        }

        // Add fee
        if ($paymentFee) {
            $aItems['surcharge'] = [
                [
                    'name' => $this->module->l('Payment Fee'),
                    'quantity' => 1,
                    'unitPrice' => round($paymentFee, $apiRoundingPrecision),
                    'totalAmount' => round($paymentFee, $apiRoundingPrecision),
                    'vatAmount' => 0,
                    'vatRate' => 0,
                ],
            ];
        }

        // Ungroup all the cart lines, just one level
        $newItems = [];
        foreach ($aItems as &$items) {
            foreach ($items as &$item) {
                $newItems[] = $item;
            }
        }

        // Convert floats to strings for the Mollie API and add additional info
        foreach ($newItems as $index => $item) {
            $line = new Line();
            $line->setName($item['name']);
            $line->setQuantity((int)$item['quantity']);
            $line->setSku(isset($item['sku']) ? $item['sku'] : '');

            $currency = Tools::strtoupper($oCurrency->iso_code);

            if (isset($item['discount'])) {
                $line->setDiscountAmount(new Amount(
                        $currency,
                        TextFormatUtility::formatNumber($item['discount'], $apiRoundingPrecision, '.', '')
                    )
                );
            }

            $line->setUnitPrice(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['unitPrice'], $apiRoundingPrecision, '.', '')
            ));

            $line->setTotalPrice(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['totalAmount'], $apiRoundingPrecision, '.', '')
            ));

            $line->setVatAmount(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['vatAmount'], $apiRoundingPrecision, '.', '')
            ));

            $line->setVatRate(TextFormatUtility::formatNumber($item['vatRate'], $apiRoundingPrecision, '.', ''));

            $newItems[$index] = $line;
        }

        return $newItems;
    }

    /**
     * Spread the cart line amount evenly
     *
     * Optionally split into multiple lines in case of rounding inaccuracies
     *
     * @param array[] $cartLineGroup Cart Line Group WITHOUT VAT details (except target VAT rate)
     * @param float $newTotal
     *
     * @return array[]
     *
     * @since 3.2.2
     * @since 3.3.3 Omits VAT details
     */
    public static function spreadCartLineGroup($cartLineGroup, $newTotal)
    {
        $apiRoundingPrecision = Mollie\Config\Config::API_ROUNDING_PRECISION;
        $newTotal = round($newTotal, $apiRoundingPrecision);
        $quantity = array_sum(array_column($cartLineGroup, 'quantity'));
        $newCartLineGroup = [];
        $spread = CartPriceUtility::spreadAmountEvenly($newTotal, $quantity);
        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = [
                'name' => $cartLineGroup[0]['name'],
                'quantity' => $qty,
                'unitPrice' => (float)$unitPrice,
                'totalAmount' => (float)$unitPrice * $qty,
                'sku' => isset($cartLineGroup[0]['sku']) ? $cartLineGroup[0]['sku'] : '',
                'targetVat' => $cartLineGroup[0]['targetVat'],
            ];
        }

        return $newCartLineGroup;
    }

}
