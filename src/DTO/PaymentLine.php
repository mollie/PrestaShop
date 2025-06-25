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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return PaymentLine
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return PaymentLine
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param string $productUrl
     *
     * @return PaymentLine
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return PaymentLine
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return PaymentLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @param string $vatRate
     *
     * @return PaymentLine
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param Amount $unitPrice
     *
     * @return PaymentLine
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param Amount $totalPrice
     *
     * @return PaymentLine
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Amount|null
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param Amount $discountAmount
     *
     * @return PaymentLine
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @param Amount $vatAmount
     *
     * @return PaymentLine
     */
    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     *
     * @return PaymentLine
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
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
