<?php

namespace Mollie\DTO;

use JsonSerializable;
use Mollie\DTO\Object\Amount;

class Line implements JsonSerializable
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
     * @var Amount
     */
    private $discountAmount;
    /**
     * @var Amount
     */
    private $vatAmount;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param mixed $productUrl
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return mixed
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param mixed $metaData
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @param mixed $vatRate
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;
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
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
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
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return Amount
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param Amount $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
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
     */
    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;
    }

    public function jsonSerialize()
    {
        return [
            "sku" => $this->getSku(),
            "name" => $this->getName(),
            "productUrl" => $this->getProductUrl(),
            "imageUrl" => $this->getImageUrl(),
            "metadata" => $this->getMetaData(),
            "quantity" => $this->getQuantity(),
            "vatRate" => $this->getVatRate(),
            "unitPrice" => [
                "currency" => $this->getUnitPrice()->getCurrency(),
                "value" => $this->getUnitPrice()->getValue()
            ],
            "totalAmount" => [
                "currency" => $this->getTotalPrice()->getCurrency(),
                "value" => $this->getTotalPrice()->getValue()
            ],
            "discountAmount" => $this->getDiscountAmount() ?
                [
                    "currency" => $this->getDiscountAmount()->getCurrency(),
                    "value" => $this->getDiscountAmount()->getValue()
                ]
                :
                [],
            "vatAmount" => [
                "currency" => $this->getVatAmount()->getCurrency(),
                "value" => $this->getVatAmount()->getValue()
            ]
        ];
    }
}