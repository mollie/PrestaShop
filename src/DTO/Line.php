<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;
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
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
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
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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