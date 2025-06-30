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

class OrderLine implements JsonSerializable
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
    private $name;

    /**
     * @var string
     */
    private $productUrl;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var array
     */
    private $metaData;

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
     * @var string
     */
    private $category;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return OrderLine
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return OrderLine
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setProductUrl(string $productUrl): self
    {
        $this->productUrl = $productUrl;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     *
     * @return OrderLine
     */
    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setVatRate(string $vatRate): self
    {
        $this->vatRate = $vatRate;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setUnitPrice(Amount $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setTotalPrice(Amount $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setDiscountAmount(Amount $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
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
     *
     * @return OrderLine
     */
    public function setVatAmount(Amount $vatAmount): self
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return OrderLine
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'sku' => $this->getSku(),
            'name' => $this->getName(),
            'productUrl' => $this->getProductUrl(),
            'imageUrl' => $this->getImageUrl(),
            'metadata' => $this->getMetaData(),
            'quantity' => $this->getQuantity(),
            'vatRate' => $this->getVatRate(),
            'category' => $this->getCategory(),
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
                [],
            'vatAmount' => [
                'currency' => $this->getVatAmount()->getCurrency(),
                'value' => $this->getVatAmount()->getValue(),
            ],
        ];
    }
}
