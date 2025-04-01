<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 */

namespace Mollie\Service\CartLine;

use Mollie\Config\Config;
use Mollie\Service\LanguageService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemShippingLineService
{
    /* @var LanguageService */
    private $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @param float $roundedShippingCost
     * @param array $cartSummary
     * @param array $orderLines
     *
     * @return array
     */
    public function addShippingLine(float $roundedShippingCost, array $cartSummary, array $orderLines): array
    {
        if (round($roundedShippingCost, 2) > 0) {
            $shippingVatRate = round(($cartSummary['total_shipping'] - $cartSummary['total_shipping_tax_exc']) / $cartSummary['total_shipping_tax_exc'] * 100, Config::API_ROUNDING_PRECISION);

            $orderLines['shipping'] = [
                [
                    'name' => $this->languageService->lang('Shipping'),
                    'quantity' => 1,
                    'unitPrice' => round($roundedShippingCost, Config::API_ROUNDING_PRECISION),
                    'totalAmount' => round($roundedShippingCost, Config::API_ROUNDING_PRECISION),
                    'vatAmount' => round($roundedShippingCost * $shippingVatRate / ($shippingVatRate + 100), Config::API_ROUNDING_PRECISION),
                    'vatRate' => $shippingVatRate,
                ],
            ];
        }

        return $orderLines;
    }
}
