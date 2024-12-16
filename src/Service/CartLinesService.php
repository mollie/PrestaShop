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
use Mollie\Service\CartLine\CartItemShippingLineService;
use Mollie\Service\CartLine\CartItemsService;
use Mollie\Service\CartLine\CartItemWrappingService;
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
     * @var LanguageService
     */
    private $languageService;
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

    public function __construct(
        LanguageService $languageService,
        CartItemsService $cartItemsService,
        CartItemDiscountService $cartItemDiscountService,
        CartItemShippingLineService $cartItemShippingLineService,
        CartItemWrappingService $cartItemWrappingService
    )
    {
        $this->languageService = $languageService;
        $this->cartItemsService = $cartItemsService;
        $this->cartItemDiscountService = $cartItemDiscountService;
        $this->cartItemShippingLineService = $cartItemShippingLineService;
        $this->cartItemWrappingService = $cartItemWrappingService;
    }

    public function buildCartLines(
        $amount, $paymentFeeData, $currencyIsoCode, $cartSummary, $shippingCost, $cartItems, $psGiftWrapping, $selectedVoucherCategory
    ) {

        $apiRoundingPrecision = Config::API_ROUNDING_PRECISION;
        $vatRatePrecision = Config::VAT_RATE_ROUNDING_PRECISION;

        $totalPrice = round($amount, $apiRoundingPrecision);
        $roundedShippingCost = round($shippingCost, $apiRoundingPrecision);

        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
        $remaining = round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            $apiRoundingPrecision
        );

        $orderLines = [];

        /* Item */
        list($orderLines, $remaining) = $this->cartItemsService->createProductLines($cartItems, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remaining);

        // Add discounts to the order lines
        $totalDiscounts = isset($cartSummary['total_discounts']) ? $cartSummary['total_discounts'] : 0;
        list($orderLines, $remaining) = $this->cartItemDiscountService->addDiscountsToProductLines($totalDiscounts, $orderLines, $remaining);


        //todo move these both methods inside some kind of utility class
        // Compensate for order total rounding inaccuracies
        $orderLines = $this->compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines);

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        $orderLines = $this->fillProductLinesWithRemainingData($orderLines, $apiRoundingPrecision, $vatRatePrecision);

        // Add shipping costs to the order lines
        $orderLines = $this->cartItemShippingLineService->addShippingLine($roundedShippingCost, $cartSummary, $orderLines);

        // Add wrapping costs to the order lines
        $orderLines  =  $this->cartItemWrappingService->addWrappingLine($wrappingPrice, $cartSummary, $vatRatePrecision, $orderLines);

        // Add payment fees to the order lines
        $orderLines = $this->paymentFeeService->addPaymentFee($orderLines, $paymentFeeData);

        $newItems = $this->ungroupLines($orderLines);

        return $this->convertToLineArray($newItems, $currencyIsoCode, $apiRoundingPrecision);
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
    ) {
        // TODO refactor whole service, split order line append into separate services and test them individually at least!!!

        $apiRoundingPrecision = Config::API_ROUNDING_PRECISION;
        $vatRatePrecision = Config::VAT_RATE_ROUNDING_PRECISION;

        $totalPrice = round($amount, $apiRoundingPrecision);
        $roundedShippingCost = round($shippingCost, $apiRoundingPrecision);
        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
        $totalDiscounts = isset($cartSummary['total_discounts']) ? $cartSummary['total_discounts'] : 0;
        $remaining = round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            $apiRoundingPrecision
        );

        $orderLines = [];
        /* Item */
//        [$orderLines, $remaining] = $this->createProductLines($cartItems, $apiRoundingPrecision, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remaining);

        // Add discount if applicable
//        [$orderLines, $remaining] = $this->addDiscountsToProductLines($totalDiscounts, $apiRoundingPrecision, $orderLines, $remaining);

        // Compensate for order total rounding inaccuracies
        $orderLines = $this->compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines);

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        $orderLines = $this->fillProductLinesWithRemainingData($orderLines, $apiRoundingPrecision, $vatRatePrecision);

        // Add shipping
//        $orderLines = $this->addShippingLine($roundedShippingCost, $cartSummary, $apiRoundingPrecision, $orderLines);

        // Add wrapping
        $orderLines = $this->addWrappingLine($wrappingPrice, $cartSummary, $vatRatePrecision, $apiRoundingPrecision, $orderLines);

        // Add fee
        $orderLines = $this->addPaymentFeeLine($paymentFeeData, $apiRoundingPrecision, $orderLines);

        // Ungroup all the cart lines, just one level
        $newItems = $this->ungroupLines($orderLines);

        // Convert floats to strings for the Mollie API and add additional info
        return $this->convertToLineArray($newItems, $currencyIsoCode, $apiRoundingPrecision);
    }

    /**
     * Spread the cart line amount evenly.
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
        $apiRoundingPrecision = Config::API_ROUNDING_PRECISION;
        $newTotal = round($newTotal, $apiRoundingPrecision);
        $quantity = array_sum(array_column($cartLineGroup, 'quantity'));
        $newCartLineGroup = [];
        $spread = CartPriceUtility::spreadAmountEvenly($newTotal, $quantity);
        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = [
                'name' => $cartLineGroup[0]['name'],
                'quantity' => $qty,
                'unitPrice' => (float) $unitPrice,
                'totalAmount' => (float) $unitPrice * $qty,
                'sku' => isset($cartLineGroup[0]['sku']) ? $cartLineGroup[0]['sku'] : '',
                'targetVat' => $cartLineGroup[0]['targetVat'],
                'category' => $cartLineGroup[0]['category'],
            ];
        }

        return $newCartLineGroup;
    }

    /**
     * @param float $remaining
     * @param int $apiRoundingPrecision
     * @param array $orderLines
     *
     * @return array
     */
    private function compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines)
    {
        $remaining = round($remaining, $apiRoundingPrecision);
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
                $orderLines[$hash] = static::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        } elseif ($remaining > 0) {
            foreach (array_reverse($orderLines) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));
                // Otherwise spread the cart line again with the updated total
                $orderLines[$hash] = static::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        }

        return $orderLines;
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
                $unitPriceNoTax = round(CalculationUtility::getUnitPriceNoTax(
                    $line['unitPrice'],
                    $targetVat
                ),
                    $apiRoundingPrecision
                );

                // Calculate VAT
                $totalAmount = $line['totalAmount'];
                $actualVatRate = 0;
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
                    'category' => $line['category'],
                    'quantity' => (int) $quantity,
                    'unitPrice' => round($unitPrice, $apiRoundingPrecision),
                    'totalAmount' => round($totalAmount, $apiRoundingPrecision),
                    'vatRate' => round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount' => round($vatAmount, $apiRoundingPrecision),
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

    /**
     * @param PaymentFeeData $paymentFeeData
     * @param int $apiRoundingPrecision
     *
     * @return array
     */
    private function addPaymentFeeLine($paymentFeeData, $apiRoundingPrecision, array $orderLines)
    {
        if (!$paymentFeeData->isActive()) {
            return $orderLines;
        }

        $orderLines['surcharge'] = [
            [
                'name' => $this->languageService->lang('Payment fee'),
                'sku' => Config::PAYMENT_FEE_SKU,
                'quantity' => 1,
                'unitPrice' => round($paymentFeeData->getPaymentFeeTaxIncl(), $apiRoundingPrecision),
                'totalAmount' => round($paymentFeeData->getPaymentFeeTaxIncl(), $apiRoundingPrecision),
                'vatAmount' => NumberUtility::minus($paymentFeeData->getPaymentFeeTaxIncl(), $paymentFeeData->getPaymentFeeTaxExcl()),
                'vatRate' => $paymentFeeData->getTaxRate(),
            ],
        ];

        return $orderLines;
    }

    /**
     * @return array
     */
    private function ungroupLines(array $orderLines)
    {
        $newItems = [];
        foreach ($orderLines as &$items) {
            foreach ($items as &$item) {
                $newItems[] = $item;
            }
        }

        return $newItems;
    }

    /**
     * @param string $currencyIsoCode
     * @param int $apiRoundingPrecision
     *
     * @return array
     */
    private function convertToLineArray(array $newItems, $currencyIsoCode, $apiRoundingPrecision)
    {
        foreach ($newItems as $index => $item) {
            $line = new Line();
            $line->setName($item['name'] ?: $item['sku']);
            $line->setQuantity((int) $item['quantity']);
            $line->setSku(isset($item['sku']) ? $item['sku'] : '');

            $currency = strtoupper(strtolower($currencyIsoCode));

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

            if (isset($item['category'])) {
                $line->setCategory($item['category']);
            }

            $line->setVatRate(TextFormatUtility::formatNumber($item['vatRate'], $apiRoundingPrecision, '.', ''));
            $line->setProductUrl($item['product_url'] ?? null);
            $line->setImageUrl($item['image_url'] ?? null);

            $newItems[$index] = $line;
        }

        return $newItems;
    }
}
