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

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Service\CartLine\CartItemDiscountService;
use Mollie\Service\CartLine\CartItemProductLinesService;
use Mollie\Service\CartLine\CartItemShippingLineService;
use Mollie\Service\CartLine\CartItemsService;
use Mollie\Service\CartLine\CartItemWrappingService;
use mollie\src\Service\CartLine\CartItemPaymentFeeService;
use mollie\src\Utility\LineUtility;
use mollie\src\Utility\RoundingUtility;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\CalculationUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartLinesService
{
    /* @var CartItemsService */
    private $cartItemsService;

    /* @var CartItemDiscountService */
    private $cartItemDiscountService;

    /* @var CartItemShippingLineService */
    private $cartItemShippingLineService;

    /* @var CartItemWrappingService */
    private $cartItemWrappingService;

    /* @var CartItemProductLinesService */
    private $cartItemProductLinesService;

    /* @var CartItemPaymentFeeService */
    private $cartItemPaymentFeeService;

    /* @var LineUtility */
    private $lineUtility;

    /* @var RoundingUtility */
    private $roundingUtility;

    /* @var ArrayUtility */
    private $arrayUtility;

    public function __construct(
        CartItemsService $cartItemsService,
        CartItemDiscountService $cartItemDiscountService,
        CartItemShippingLineService $cartItemShippingLineService,
        CartItemWrappingService $cartItemWrappingService,
        CartItemProductLinesService $cartItemProductLinesService,
        CartItemPaymentFeeService $cartItemPaymentFeeService,
        LineUtility $lineUtility,
        RoundingUtility $roundingUtility,
        ArrayUtility $arrayUtility
    ) {
        $this->cartItemsService = $cartItemsService;
        $this->cartItemDiscountService = $cartItemDiscountService;
        $this->cartItemShippingLineService = $cartItemShippingLineService;
        $this->cartItemWrappingService = $cartItemWrappingService;
        $this->cartItemProductLinesService = $cartItemProductLinesService;
        $this->cartItemPaymentFeeService = $cartItemPaymentFeeService;
        $this->lineUtility = $lineUtility;
        $this->roundingUtility = $roundingUtility;
        $this->arrayUtility = $arrayUtility;
    }

    /**
     * @param float $amount
     * @param PaymentFeeData $paymentFeeData
     * @param string $currencyIsoCode
     * @param array $cartSummary
     * @param float $shippingCost
     * @param array $cartItems
     * @param bool $psGiftWrapping
     * @param string $selectedVoucherCategory
     *
     * @return array
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function getCartLines(
        $amount,
        $paymentFeeData,
        $currencyIsoCode,
        $cartSummary,
        $shippingCost,
        $cartItems,
        $psGiftWrapping,
        $selectedVoucherCategory
    ): array {
        $totalPrice = $this->roundingUtility->round($amount, Config::API_ROUNDING_PRECISION);
        $roundedShippingCost = $this->roundingUtility->round($shippingCost, Config::API_ROUNDING_PRECISION);

        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? $this->roundingUtility->round($cartSummary['total_wrapping'], Config::API_ROUNDING_PRECISION) : 0;

        $remainingAmount = $this->roundingUtility->round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            Config::API_ROUNDING_PRECISION
        );

        $orderLines = [];

        // Item
        list($orderLines, $remainingAmount) = $this->cartItemsService->createProductLines($cartItems, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remainingAmount);

        // Add discounts to the order lines
        $totalDiscounts = $cartSummary['total_discounts'] ?? 0;
        list($orderLines, $remainingAmount) = $this->cartItemDiscountService->addDiscountsToProductLines($totalDiscounts, $orderLines, $remainingAmount);

        // Compensate for order total rounding inaccuracies
        $orderLines = $this->roundingUtility->compositeRoundingInaccuracies($remainingAmount, $orderLines);

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        $orderLines = $this->cartItemProductLinesService->fillProductLinesWithRemainingData($orderLines, Config::VAT_RATE_ROUNDING_PRECISION);

        // Add shipping costs to the order lines
        $orderLines = $this->cartItemShippingLineService->addShippingLine($roundedShippingCost, $cartSummary, $orderLines);

        // Add wrapping costs to the order lines
        $orderLines = $this->cartItemWrappingService->addWrappingLine($wrappingPrice, $cartSummary, Config::VAT_RATE_ROUNDING_PRECISION, $orderLines);

        // Add payment fees to the order lines
        $orderLines = $this->cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeData, $orderLines);

        $newItems = $this->arrayUtility->ungroupLines($orderLines);

        return $this->lineUtility->convertToLineArray($newItems, $currencyIsoCode);
    }
}
