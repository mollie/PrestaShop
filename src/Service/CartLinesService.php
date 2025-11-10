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

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\DTO\Object\Amount;
use Mollie\DTO\OrderLine;
use Mollie\DTO\PaymentLine;
use Mollie\Exception\CouldNotProcessCartLinesException;
use Mollie\Service\CartLine\CartItemDiscountService;
use Mollie\Service\CartLine\CartItemProductLinesService;
use Mollie\Service\CartLine\CartItemShippingLineService;
use Mollie\Service\CartLine\CartItemsService;
use Mollie\Service\CartLine\CartItemWrappingService;
use mollie\src\Service\CartLine\CartItemPaymentFeeService;
use mollie\src\Utility\LineUtility;
use mollie\src\Utility\RoundingUtility;
use Mollie\Utility\ArrayUtility;
use Mollie\Adapter\Context;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Enum\LineType;
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
     * @param string $lineType
     *
     * @return array
     *
     * @throws CouldNotProcessCartLinesException
     */
    public function getCartLines(
<<<<<<< HEAD
        float $amount,
        PaymentFeeData $paymentFeeData,
        string $currencyIsoCode,
        array $cartSummary,
        float $shippingCost,
        array $cartItems,
        bool $psGiftWrapping,
        string $selectedVoucherCategory
    ): array {
        $totalPrice = round($amount, Config::API_ROUNDING_PRECISION);
        $roundedShippingCost = round($shippingCost, Config::API_ROUNDING_PRECISION);
=======
        $amount,
        $paymentFeeData,
        $currencyIsoCode,
        $cartSummary,
        $shippingCost,
        $cartItems,
        $psGiftWrapping,
        $selectedVoucherCategory,
        $lineType
    ) {
        // TODO refactor whole service, split order line append into separate services and test them individually at least!!!
>>>>>>> b977487051973766eca407bbb4ec66ddb34229a6

        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? round($cartSummary['total_wrapping'], Config::API_ROUNDING_PRECISION) : 0;

        $remainingAmount = round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            Config::API_ROUNDING_PRECISION
        );

        $orderLines = [];
<<<<<<< HEAD
        try {
            list($orderLines, $remainingAmount) = $this->cartItemsService->createProductLines($cartItems, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remainingAmount);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToCreateProductLines($e);
=======
        /* Item */
        list($orderLines, $remaining) = $this->createProductLines($cartItems, $apiRoundingPrecision, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remaining);

        // Add discount if applicable
        list($orderLines, $remaining) = $this->addDiscountsToProductLines($totalDiscounts, $apiRoundingPrecision, $orderLines, $remaining);

        // Compensate for order total rounding inaccuracies
        $orderLines = $this->compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines, $paymentFeeData);

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        $orderLines = $this->fillProductLinesWithRemainingData($orderLines, $apiRoundingPrecision, $vatRatePrecision);

        // Add shipping
        $orderLines = $this->addShippingLine($roundedShippingCost, $cartSummary, $apiRoundingPrecision, $orderLines);

        // Add wrapping
        $orderLines = $this->addWrappingLine($wrappingPrice, $cartSummary, $vatRatePrecision, $apiRoundingPrecision, $orderLines);

        // Add fee
        $orderLines = $this->addPaymentFeeLine($paymentFeeData, $apiRoundingPrecision, $orderLines);

        // Ungroup all the cart lines, just one level
        $newItems = $this->ungroupLines($orderLines);

        // Convert floats to strings for the Mollie API and add additional info
        return $this->convertToLineArray($newItems, $currencyIsoCode, $apiRoundingPrecision, $lineType);
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
                'type' => $cartLineGroup[0]['type'],
                'quantity' => $qty,
                'unitPrice' => (float) $unitPrice,
                'totalAmount' => (float) $unitPrice * $qty,
                'sku' => isset($cartLineGroup[0]['sku']) ? $cartLineGroup[0]['sku'] : '',
                'targetVat' => $cartLineGroup[0]['targetVat'],
                'categories' => $cartLineGroup[0]['categories'],
                'product_url' => isset($cartLineGroup[0]['product_url']) ? $cartLineGroup[0]['product_url'] : '',
                'image_url' => isset($cartLineGroup[0]['image_url']) ? $cartLineGroup[0]['image_url'] : '',
            ];
>>>>>>> b977487051973766eca407bbb4ec66ddb34229a6
        }

        $totalDiscounts = $cartSummary['total_discounts'] ?? 0;

<<<<<<< HEAD
        try {
            list($orderLines, $remainingAmount) = $this->cartItemDiscountService->addDiscountsToProductLines($totalDiscounts, $orderLines, $remainingAmount);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddDiscountsToProductLines($e);
        }

        try {
            $orderLines = $this->roundingUtility->compositeRoundingInaccuracies($remainingAmount, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToRoundAmount($e);
        }

        try {
            $orderLines = $this->cartItemProductLinesService->fillProductLinesWithRemainingData($orderLines, Config::VAT_RATE_ROUNDING_PRECISION);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToFillProductLinesWithRemainingData($e);
        }

        try {
            $orderLines = $this->cartItemShippingLineService->addShippingLine($roundedShippingCost, $cartSummary, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddShippingLine($e);
        }

        try {
            $orderLines = $this->cartItemWrappingService->addWrappingLine($wrappingPrice, $cartSummary, Config::VAT_RATE_ROUNDING_PRECISION, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddWrappingLine($e);
        }

        try {
            $orderLines = $this->cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeData, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddPaymentFee($e);
=======
    /**
     * @param int $apiRoundingPrecision
     * @param array $giftProducts
     * @param string $selectedVoucherCategory
     * @param float $remaining
     *
     * @return array
     */
    private function createProductLines(array $cartItems, $apiRoundingPrecision, $giftProducts, array $orderLines, $selectedVoucherCategory, $remaining)
    {
        foreach ($cartItems as $cartItem) {
            // Get the rounded total w/ tax
            $roundedTotalWithTax = round($cartItem['total_wt'], $apiRoundingPrecision);

            // Skip if no qty
            $quantity = (int) $cartItem['cart_quantity'];
            if ($quantity <= 0 || $cartItem['price_wt'] <= 0) {
                continue;
            }

            // Generate the product hash
            $idProduct = TextFormatUtility::formatNumber($cartItem['id_product'], 0);
            $idProductAttribute = TextFormatUtility::formatNumber($cartItem['id_product_attribute'], 0);
            $idCustomization = TextFormatUtility::formatNumber($cartItem['id_customization'], 0);

            $productHash = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}";

            foreach ($giftProducts as $gift_product) {
                if ($gift_product['id_product'] === $cartItem['id_product']) {
                    $quantity = NumberUtility::minus($quantity, $gift_product['cart_quantity']);

                    $productHashGift = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}gift";
                    $orderLines[$productHashGift][] = [
                        'name' => $cartItem['name'],
                        'type' => 'physical',
                        'sku' => $productHashGift,
                        'targetVat' => (float) $cartItem['rate'],
                        'quantity' => $gift_product['cart_quantity'],
                        'unitPrice' => 0,
                        'totalAmount' => 0,
                        'categories' => [],
                        'product_url' => $this->context->getProductLink($cartItem['id_product']),
                        'image_url' => $this->context->getImageLink($cartItem['link_rewrite'], $cartItem['id_image']),
                        'metadata' => [
                            'idProduct' => $cartItem['id_product'],
                        ],
                    ];
                    continue;
                }
            }

            if ((int) $quantity <= 0) {
                continue;
            }

            // Try to spread this product evenly and account for rounding differences on the order line
            $orderLines[$productHash][] = [
                'name' => $cartItem['name'],
                'type' => 'physical',
                'sku' => $productHash,
                'targetVat' => (float) $cartItem['rate'],
                'quantity' => $quantity,
                'unitPrice' => round($cartItem['price_wt'], $apiRoundingPrecision),
                'totalAmount' => (float) $roundedTotalWithTax,
                'categories' => $this->voucherService->getVoucherCategory($cartItem, $selectedVoucherCategory),
                'product_url' => $this->context->getProductLink($cartItem['id_product']),
                'image_url' => $this->context->getImageLink($cartItem['link_rewrite'], $cartItem['id_image']),
                'metadata' => [
                    'idProduct' => $cartItem['id_product'],
                ],
            ];
            $remaining -= $roundedTotalWithTax;
        }

        return [$orderLines, $remaining];
    }

    /**
     * @param float $totalDiscounts
     * @param int $apiRoundingPrecision
     * @param array $orderLines
     * @param float $remaining
     *
     * @return array
     */
    private function addDiscountsToProductLines($totalDiscounts, $apiRoundingPrecision, $orderLines, $remaining)
    {
        if ($totalDiscounts >= 0.01) {
            $orderLines['discount'] = [
                [
                    'name' => 'Discount',
                    'sku' => 'DISCOUNT',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => -round($totalDiscounts, $apiRoundingPrecision),
                    'totalAmount' => -round($totalDiscounts, $apiRoundingPrecision),
                    'targetVat' => 0,
                    'categories' => [],
                ],
            ];
            $remaining = NumberUtility::plus($remaining, $totalDiscounts);
        }

        return [$orderLines, $remaining];
    }

    /**
     * @param float $remaining
     * @param int $apiRoundingPrecision
     * @param array $orderLines
     * @param PaymentFeeData $paymentFeeData
     *
     * @return array
     */
    private function compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines, $paymentFeeData)
    {
        $paymentFeeDiff = NumberUtility::minus($paymentFeeData->getPaymentFeeTaxIncl(), $remaining);

        if ($paymentFeeData->isActive() && $paymentFeeDiff < 0.1) {
            return $orderLines;
        }

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
                    'categories' => $line['categories'],
                    'type' => $line['type'] ?? null,
                    'quantity' => (int) $quantity,
                    'unitPrice' => round($unitPrice, $apiRoundingPrecision),
                    'totalAmount' => round($totalAmount, $apiRoundingPrecision),
                    'vatRate' => round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount' => round($vatAmount, $apiRoundingPrecision),
                    'product_url' => $line['product_url'] ?? null,
                    'image_url' => $line['image_url'] ?? null,
                    'metadata' => $line['metadata'] ?? [],
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
     * @param float $roundedShippingCost
     * @param array $cartSummary
     * @param int $apiRoundingPrecision
     *
     * @return array
     */
    private function addShippingLine($roundedShippingCost, $cartSummary, $apiRoundingPrecision, array $orderLines)
    {
        if (round($roundedShippingCost, 2) > 0) {
            $shippingVatRate = round(($cartSummary['total_shipping'] - $cartSummary['total_shipping_tax_exc']) / $cartSummary['total_shipping_tax_exc'] * 100, $apiRoundingPrecision);

            $orderLines['shipping'] = [
                [
                    'name' => $this->languageService->lang('Shipping'),
                    'type' => 'shipping_fee',
                    'quantity' => 1,
                    'unitPrice' => round($roundedShippingCost, $apiRoundingPrecision),
                    'totalAmount' => round($roundedShippingCost, $apiRoundingPrecision),
                    'vatAmount' => round($roundedShippingCost * $shippingVatRate / ($shippingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $shippingVatRate,
                ],
            ];
        }

        return $orderLines;
    }

    /**
     * @param float $wrappingPrice
     * @param int $vatRatePrecision
     * @param int $apiRoundingPrecision
     *
     * @return array
     */
    private function addWrappingLine($wrappingPrice, array $cartSummary, $vatRatePrecision, $apiRoundingPrecision, array $orderLines)
    {
        if (round($wrappingPrice, 2) > 0) {
            $wrappingVatRate = round(
                CalculationUtility::getActualVatRate(
                    $cartSummary['total_wrapping'],
                    $cartSummary['total_wrapping_tax_exc']
                ),
                $vatRatePrecision
            );

            $orderLines['wrapping'] = [
                [
                    'name' => $this->languageService->lang('Gift wrapping'),
                    'type' => 'surcharge',
                    'quantity' => 1,
                    'unitPrice' => round($wrappingPrice, $apiRoundingPrecision),
                    'totalAmount' => round($wrappingPrice, $apiRoundingPrecision),
                    'vatAmount' => round($wrappingPrice * $wrappingVatRate / ($wrappingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $wrappingVatRate,
                ],
            ];
>>>>>>> b977487051973766eca407bbb4ec66ddb34229a6
        }

        try {
            $newItems = $this->arrayUtility->ungroupLines($orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToUngroupLines($e);
        }

<<<<<<< HEAD
        try {
            $lines = $this->lineUtility->convertToLineArray($newItems, $currencyIsoCode);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedConvertToLineArray($e);
        }

        return $lines;
=======
        $orderLines['surcharge'] = [
            [
                'name' => $this->languageService->lang('Payment fee'),
                'sku' => Config::PAYMENT_FEE_SKU,
                'type' => 'surcharge',
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
     * @param array $newItems
     * @param string $currencyIsoCode
     * @param int $apiRoundingPrecision
     * @param string $lineType
     *
     * @return array
     */
    private function convertToLineArray(array $newItems, string $currencyIsoCode, int $apiRoundingPrecision, string $lineType): array
    {
        foreach ($newItems as $index => $item) {
            $lineClass = $lineType === LineType::PAYMENT ? PaymentLine::class : OrderLine::class;

            /** @var OrderLine|PaymentLine $line */
            $line = new $lineClass();

            switch ($lineType) {
                case LineType::ORDER:
                    $line->setName($item['name'] ?: $item['sku']);
                    $line->setMetaData($item['metadata'] ?? []);
                    break;
                case LineType::PAYMENT:
                    $line->setDescription($item['description'] ?? $item['name'] ?? 'N/A');
                    break;
            }

            $line->setQuantity((int) $item['quantity']);
            $line->setSku(isset($item['sku']) ? $item['sku'] : $item['name']);
            $line->setType($item['type'] ?? null);

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

            if (isset($item['categories'])) {
                switch ($lineType) {
                    case LineType::PAYMENT:
                        $categories = is_array($item['categories']) ? $item['categories'] : [$item['categories']];
                        $line->setCategories($categories);
                        break;
                    case LineType::ORDER:
                        $category = is_array($item['categories']) ? $item['categories'][0] ?? '' : $item['categories'];
                        $line->setCategory($category);
                        break;
                }
            }

            $line->setVatRate(TextFormatUtility::formatNumber($item['vatRate'], $apiRoundingPrecision, '.', ''));

            if (isset($item['product_url'])) {
                $line->setProductUrl(
                    TextFormatUtility::replaceAccentedChars((string) $item['product_url']) ?: ''
                );
            }

            if (isset($item['image_url'])) {
                $line->setImageUrl(
                    TextFormatUtility::replaceAccentedChars((string) $item['image_url']) ?: ''
                );
            }

            $newItems[$index] = $line;
        }

        return $newItems;
>>>>>>> b977487051973766eca407bbb4ec66ddb34229a6
    }
}
