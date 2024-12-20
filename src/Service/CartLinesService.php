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
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
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
use Mollie\Utility\CartPriceUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextFormatUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartLinesService
{
    /**
     * @var CartItemsService
     */
    private $cartItemsService;
    /**
     * @var CartItemDiscountService
     */
    private $cartItemDiscountService;
    private $cartItemShippingLineService;
    private $cartItemWrappingService;
    private $cartItemProductLinesService;
    private $cartItemPaymentFeeService;
    private $lineUtility;
    private $roundingUtility;
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
    )
    {
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

    // new
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
    public function buildCartLines(
        $amount,
        $paymentFeeData,
        $currencyIsoCode,
        $cartSummary,
        $shippingCost,
        $cartItems,
        $psGiftWrapping,
        $selectedVoucherCategory
    ) {
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
    // old
    public function getCartLines(
        $amount,
        $paymentFeeData,
        $currencyIsoCode,
        $cartSummary,
        $shippingCost,
        $cartItems,
        $psGiftWrapping,
        $selectedVoucherCategory
    ) {
        // TODO refactor whole service, split order line append into separate services and test them individually at least!!!

        $apiRoundingPrecision = Config::API_ROUNDING_PRECISION;
        $vatRatePrecision = Config::VAT_RATE_ROUNDING_PRECISION;

        $totalPrice = $this->roundingUtility->round($amount, $apiRoundingPrecision);
        $roundedShippingCost = $this->roundingUtility->round($shippingCost, $apiRoundingPrecision);
        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? $this->roundingUtility->round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
        $totalDiscounts = isset($cartSummary['total_discounts']) ? $cartSummary['total_discounts'] : 0;
        $remaining = $this->roundingUtility->round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            $apiRoundingPrecision
        );

        $orderLines = [];
        /* Item */
        [$orderLines, $remaining] = $this->cartItemsService->createProductLines($cartItems, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remaining);

        // Add discount if applicable
        [$orderLines, $remaining] = $this->cartItemDiscountService->addDiscountsToProductLines($totalDiscounts, $orderLines, $remaining);

        // Compensate for order total rounding inaccuracies
        $orderLines = $this->roundingUtility->compositeRoundingInaccuracies($remaining, $orderLines);

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        $orderLines = $this->cartItemProductLinesService->fillProductLinesWithRemainingData($orderLines, $vatRatePrecision);

        // Add shipping
        $orderLines = $this->cartItemShippingLineService->addShippingLine($roundedShippingCost, $cartSummary, $orderLines);

        // Add wrapping
        $orderLines = $this->cartItemWrappingService->addWrappingLine($wrappingPrice, $cartSummary, $vatRatePrecision, $orderLines);

        // Add fee
        $orderLines = $this->cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeData, $orderLines);

        // Ungroup all the cart lines, just one level
        $newItems = $this->ungroupLines($orderLines);

        // Convert floats to strings for the Mollie API and add additional info
        return $this->lineUtility->convertToLineArray($newItems, $currencyIsoCode, $apiRoundingPrecision);
    }

    /**
     * @param int $apiRoundingPrecision
     * @param int $vatRatePrecision
     *
     * @return array
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    private function fillProductLinesWithRemainingData(array $orderLines, $apiRoundingPrecision, $vatRatePrecision)
    {
        foreach ($orderLines as $productHash => $aItem) {
            $orderLines[$productHash] = array_map(function ($line) use ($apiRoundingPrecision, $vatRatePrecision) {
                $quantity = (int) $line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = $this->roundingUtility->round(CalculationUtility::getUnitPriceNoTax(
                    $line['unitPrice'],
                    $targetVat
                ),
                    $apiRoundingPrecision
                );

                // Calculate VAT
                $totalAmount = $line['totalAmount'];
                $actualVatRate = 0;
                if ($unitPriceNoTax > 0) {
                    $actualVatRate = $this->roundingUtility->round(
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
                    'category' => $line['category'],
                    'quantity' => (int) $quantity,
                    'unitPrice' => $this->roundingUtility->round($unitPrice, $apiRoundingPrecision),
                    'totalAmount' => $this->roundingUtility->round($totalAmount, $apiRoundingPrecision),
                    'vatRate' => $this->roundingUtility->round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount' => $this->roundingUtility->round($vatAmount, $apiRoundingPrecision),
                    'product_url' => $line['product_url'] ?? null,
                    'image_url' => $line['image_url'] ?? null,
                ];
                if (isset($line['sku'])) {
                    $newItem['sku'] = $line['sku'];
                }

                return $newItem;
            }, $aItem);
        }

        return $orderLines;
    }
}
