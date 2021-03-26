<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Cart;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
use Mollie\Utility\CalculationUtility;
use Mollie\Utility\CartPriceUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextFormatUtility;

class CartLinesService
{
	/**
	 * @var VoucherService
	 */
	private $voucherService;

	/**
	 * @var LanguageService
	 */
	private $languageService;

	/**
	 * @var ToolsAdapter
	 */
	private $tools;

	public function __construct(LanguageService $languageService, VoucherService $voucherService, ToolsAdapter $tools)
	{
		$this->voucherService = $voucherService;
		$this->languageService = $languageService;
		$this->tools = $tools;
	}

	/**
	 * @param float $amount
	 * @param float $paymentFee
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
		$paymentFee,
		$currencyIsoCode,
		$cartSummary,
		$shippingCost,
		$cartItems,
		$psGiftWrapping,
		$selectedVoucherCategory
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
		$totalDiscounts = isset($cartSummary['total_discounts']) ? $cartSummary['total_discounts'] : 0;
		$remaining = round(
			CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
			$apiRoundingPrecision
		);

		$orderLines = [];
		/* Item */
		list($orderLines, $remaining) = $this->createProductLines($cartItems, $apiRoundingPrecision, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remaining);

		// Add discount if applicable
		list($orderLines, $remaining) = $this->addDiscountsToProductLines($totalDiscounts, $apiRoundingPrecision, $orderLines, $remaining);

		// Compensate for order total rounding inaccuracies
		$orderLines = $this->compositeRoundingInaccuracies($remaining, $apiRoundingPrecision, $orderLines);

		// Fill the order lines with the rest of the data (tax, total amount, etc.)
		$orderLines = $this->fillProductLinesWithRemainingData($orderLines, $apiRoundingPrecision, $vatRatePrecision);

		// Add shipping
		$orderLines = $this->addShippingLine($roundedShippingCost, $cartSummary, $apiRoundingPrecision, $orderLines);

		// Add wrapping
		$orderLines = $this->addWrappingLine($wrappingPrice, $cartSummary, $vatRatePrecision, $apiRoundingPrecision, $orderLines);

		// Add fee
		$orderLines = $this->addPaymentFeeLine($paymentFee, $apiRoundingPrecision, $orderLines);

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
	 * @param array $cartItems
	 * @param int $apiRoundingPrecision
	 * @param array $giftProducts
	 * @param array $orderLines
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
						'sku' => $productHashGift,
						'targetVat' => (float) $cartItem['rate'],
						'quantity' => $gift_product['cart_quantity'],
						'unitPrice' => 0,
						'totalAmount' => 0,
						'category' => '',
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
				'sku' => $productHash,
				'targetVat' => (float) $cartItem['rate'],
				'quantity' => $quantity,
				'unitPrice' => round($cartItem['price_wt'], $apiRoundingPrecision),
				'totalAmount' => (float) $roundedTotalWithTax,
				'category' => $this->voucherService->getVoucherCategory($cartItem, $selectedVoucherCategory),
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
					'type' => 'discount',
					'quantity' => 1,
					'unitPrice' => -round($totalDiscounts, $apiRoundingPrecision),
					'totalAmount' => -round($totalDiscounts, $apiRoundingPrecision),
					'targetVat' => 0,
					'category' => '',
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
				$orderLines[$hash] = static::spreadCartLineGroup($items, $totalAmount - $remaining);
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
	 * @param array $orderLines
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
	 * @param array $orderLines
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
	 * @param array $cartSummary
	 * @param int $vatRatePrecision
	 * @param int $apiRoundingPrecision
	 * @param array $orderLines
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
					'quantity' => 1,
					'unitPrice' => round($wrappingPrice, $apiRoundingPrecision),
					'totalAmount' => round($wrappingPrice, $apiRoundingPrecision),
					'vatAmount' => round($wrappingPrice * $wrappingVatRate / ($wrappingVatRate + 100), $apiRoundingPrecision),
					'vatRate' => $wrappingVatRate,
				],
			];
		}

		return $orderLines;
	}

	/**
	 * @param float $paymentFee
	 * @param int $apiRoundingPrecision
	 * @param array $orderLines
	 *
	 * @return array
	 */
	private function addPaymentFeeLine($paymentFee, $apiRoundingPrecision, array $orderLines)
	{
		if ($paymentFee) {
			$orderLines['surcharge'] = [
				[
					'name' => $this->languageService->lang('Payment Fee'),
					'quantity' => 1,
					'unitPrice' => round($paymentFee, $apiRoundingPrecision),
					'totalAmount' => round($paymentFee, $apiRoundingPrecision),
					'vatAmount' => 0,
					'vatRate' => 0,
				],
			];
		}

		return $orderLines;
	}

	/**
	 * @param array $orderLines
	 *
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

			$currency = $this->tools->strtoupper($currencyIsoCode);

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

			$newItems[$index] = $line;
		}

		return $newItems;
	}
}
