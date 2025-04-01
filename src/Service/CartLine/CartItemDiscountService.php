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

namespace Mollie\Service\CartLine;

use Mollie\Config\Config;
use Mollie\Utility\NumberUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemDiscountService
{
    /**
     * @param float $totalDiscounts
     * @param array $orderLines
     * @param float $remaining
     *
     * @return array
     */
    public function addDiscountsToProductLines(float $totalDiscounts, array $orderLines, float $remaining): array
    {
        if ($totalDiscounts >= 0.01) {
            $orderLines['discount'] = [
                [
                    'name' => 'Discount',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => -round($totalDiscounts, Config::API_ROUNDING_PRECISION),
                    'totalAmount' => -round($totalDiscounts, Config::API_ROUNDING_PRECISION),
                    'targetVat' => 0,
                    'category' => '',
                ],
            ];

            $remaining = NumberUtility::plus($remaining, $totalDiscounts);
        }

        return [$orderLines, $remaining];
    }
}
