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

namespace mollie\src\Utility;

use Mollie\Config\Config;
use Mollie\Service\CartLine\CartItemsService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RoundingUtility
{
    /**
     * @param float $remaining
     * @param array $orderLines
     *
     * @return array
     */
    public function compositeRoundingInaccuracies($remaining, $orderLines)
    {
        $remaining = round($remaining, CONFIG::API_ROUNDING_PRECISION);
        if ($remaining < 0) {
            foreach (array_reverse($orderLines) as $hash => $items) {
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
                //TODO: check why remaining comes -100 when testing and new total becomes different
                $orderLines[$hash] = CartItemsService::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        } elseif ($remaining > 0) {
            foreach (array_reverse($orderLines) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));
                // Otherwise spread the cart line again with the updated total
                $orderLines[$hash] = CartItemsService::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        }

        return $orderLines;
    }
}
