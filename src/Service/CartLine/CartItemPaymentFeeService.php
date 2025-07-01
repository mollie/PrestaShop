<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace mollie\src\Service\CartLine;

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Service\LanguageService;
use Mollie\Utility\NumberUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemPaymentFeeService
{
    /* @var LanguageService */
    private $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @param PaymentFeeData $paymentFeeData
     * @param array $orderLines
     *
     * @return array
     */
    public function addPaymentFeeLine(PaymentFeeData $paymentFeeData, array $orderLines): array
    {
        if (!$paymentFeeData->isActive()) {
            return $orderLines;
        }

        $orderLines['surcharge'] = [
            [
                'name' => $this->languageService->lang('Payment fee'),
                'sku' => Config::PAYMENT_FEE_SKU,
                'quantity' => 1,
                'unitPrice' => round($paymentFeeData->getPaymentFeeTaxIncl(), CONFIG::API_ROUNDING_PRECISION),
                'totalAmount' => round($paymentFeeData->getPaymentFeeTaxIncl(), CONFIG::API_ROUNDING_PRECISION),
                'vatAmount' => NumberUtility::minus($paymentFeeData->getPaymentFeeTaxIncl(), $paymentFeeData->getPaymentFeeTaxExcl()),
                'vatRate' => $paymentFeeData->getTaxRate(),
            ],
        ];

        return $orderLines;
    }
}
