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

namespace Mollie\DTO;

use JsonSerializable;
use Mollie\DTO\Object\Amount;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentLine implements JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $productUrl;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $vatRate;

    /**
     * @var Amount
     */
    private $unitPrice;

    /**
     * @var Amount
     */
    private $totalPrice;

    /**
     * @var Amount|null
     */
    private $discountAmount = null;

    /**
     * @var Amount
     */
    private $vatAmount;

    /**
     * @var array
     */
    private $categories;

    /**
     * @var string|null
     */
    private $description = null;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getProductUrl(): string
    {
        return $this->productUrl;
    }

    /**
     * @param string $productUrl
     */
    public function setProductUrl(string $productUrl): void
    {
        $this->productUrl = $productUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getVatRate(): string
    {
        return $this->vatRate;
    }

    /**
     * @param string $vatRate
     */
    public function setVatRate(string $vatRate): void
    {
        $this->vatRate = $vatRate;
    }

    /**
     * @return Amount
     */
    public function getUnitPrice(): Amount
    {
        return $this->unitPrice;
    }

    /**
     * @param Amount $unitPrice
     */
    public function setUnitPrice(Amount $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return Amount
     */
    public function getTotalPrice(): Amount
    {
        return $this->totalPrice;
    }

    /**
     * @param Amount $totalPrice
     */
    public function setTotalPrice(Amount $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return Amount|null
     */
    public function getDiscountAmount(): ?Amount
    {
        return $this->discountAmount;
    }

    /**
     * @param Amount $discountAmount
     */
    public function setDiscountAmount(Amount $discountAmount): void
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return Amount
     */
    public function getVatAmount(): Amount
    {
        return $this->vatAmount;
    }

    /**
     * @param Amount $vatAmount
     */
    public function setVatAmount(Amount $vatAmount): void
    {
        $this->vatAmount = $vatAmount;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function jsonSerialize(): array
    {
        return [
            'sku' => $this->getSku(),
            'description' => $this->getDescription(),
            'productUrl' => $this->getProductUrl(),
            'imageUrl' => $this->getImageUrl(),
            'quantity' => $this->getQuantity(),
            'vatRate' => $this->getVatRate(),
            'categories' => $this->getCategories(),
            'unitPrice' => [
                'currency' => $this->getUnitPrice()->getCurrency(),
                'value' => $this->getUnitPrice()->getValue(),
            ],
            'totalAmount' => [
                'currency' => $this->getTotalPrice()->getCurrency(),
                'value' => $this->getTotalPrice()->getValue(),
            ],
            'discountAmount' => $this->getDiscountAmount() ?
                [
                    'currency' => $this->getDiscountAmount()->getCurrency(),
                    'value' => $this->getDiscountAmount()->getValue(),
                ]
                :
                [
                    'currency' => $this->getUnitPrice()->getCurrency(),
                    'value' => '0.00',
                ],
            'vatAmount' => [
                'currency' => $this->getVatAmount()->getCurrency(),
                'value' => $this->getVatAmount()->getValue(),
            ],
        ];
    }
}
