<?php

namespace Mollie\Service;

use Cart;
use Configuration;
use Currency;
use Mollie;
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
     * @return array
     *
     */
    public function getCartLines($amount, $paymentFee, Cart $cart)
    {
        $oCurrency = new Currency($cart->id_currency);
        $apiRoundingPrecision = Mollie\Config\Config::API_ROUNDING_PRECISION; // PHP 5.3, closures and static access, not a good combo :(

        $remaining = round($amount, $apiRoundingPrecision);
        $shipping = round($cart->getTotalShippingCost(null, true), $apiRoundingPrecision);
        $cartSummary = $cart->getSummaryDetails();
        $cartItems = $cart->getProducts();
        $wrapping = Configuration::get('PS_GIFT_WRAPPING') ? round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
        $remaining = round($remaining - $shipping - $wrapping, $apiRoundingPrecision);
        $totalDiscounts = isset($cartSummary['total_discounts']) ? round($cartSummary['total_discounts'], $apiRoundingPrecision) : 0;

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

            // Try to spread this product evenly and account for rounding differences on the order line
            foreach (\Mollie\Utility\CartPriceUtility::spreadAmountEvenly($roundedTotalWithTax, $quantity) as $unitPrice => $qty) {
                $aItems[$productHash][] = [
                    'name' => $cartItem['name'],
                    'sku' => $productHash,
                    'targetVat' => (float)$cartItem['rate'],
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => (float)$unitPrice * $qty,
                ];
                $remaining -= round((float)$unitPrice * $qty, $apiRoundingPrecision);
            }
        }

        // Add discount if applicable
        if ($totalDiscounts >= 0.01) {
            $totalDiscountsNoTax = round($cartSummary['total_discounts_tax_exc'], $apiRoundingPrecision);
            $vatRate = round((($totalDiscounts - $totalDiscountsNoTax) / $totalDiscountsNoTax) * 100, $apiRoundingPrecision);

            $aItems['discount'] = [
                [
                    'name' => 'Discount',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => -round($totalDiscounts, $apiRoundingPrecision),
                    'totalAmount' => -round($totalDiscounts, $apiRoundingPrecision),
                    'targetVat' => $vatRate,
                ],
            ];
            $remaining += $totalDiscounts;
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
            $aItems[$productHash] = array_map(function ($line) use ($apiRoundingPrecision, $oCurrency) {
                $quantity = (int)$line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = round($line['unitPrice'] / (1 + ($targetVat / 100)), $apiRoundingPrecision);

                // Calculate VAT
                $totalAmount = round($unitPrice * $quantity, $apiRoundingPrecision);
                $actualVatRate = round(($unitPrice * $quantity - $unitPriceNoTax * $quantity) / ($unitPriceNoTax * $quantity) * 100, $apiRoundingPrecision);
                $vatAmount = $totalAmount * ($actualVatRate / ($actualVatRate + 100));

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
            $wrappingVatRate = round(($cartSummary['total_wrapping'] - $cartSummary['total_wrapping_tax_exc']) / $cartSummary['total_wrapping_tax_exc'] * 100, $apiRoundingPrecision);

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
            $newItems[$index] = [
                'name' => (string)$item['name'],
                'quantity' => (int)$item['quantity'],
                'sku' => (string)(isset($item['sku']) ? $item['sku'] : ''),
                'unitPrice' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['unitPrice'], $apiRoundingPrecision, '.', ''),
                ],
                'totalAmount' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['totalAmount'], $apiRoundingPrecision, '.', ''),
                ],
                'vatAmount' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['vatAmount'], $apiRoundingPrecision, '.', ''),
                ],
                'vatRate' => number_format($item['vatRate'], $apiRoundingPrecision, '.', ''),
            ];
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
        $spread = \Mollie\Utility\CartPriceUtility::spreadAmountEvenly($newTotal, $quantity);
        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = [
                'name' => $cartLineGroup[0]['name'],
                'quantity' => $qty,
                'unitPrice' => (float)$unitPrice,
                'totalAmount' => (float)$unitPrice * $qty,
                'sku' => $cartLineGroup[0]['sku'],
                'targetVat' => $cartLineGroup[0]['targetVat'],
            ];
        }

        return $newCartLineGroup;
    }

}