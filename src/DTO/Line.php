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
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
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
	 * @var string
	 */
	private $category;

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
	 * @return Line
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
	 * @return Line
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;

		return $this;
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
	 *
	 * @return Line
	 */
	public function setName($name)
	{
		$this->name = $name;

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
	 * @return Line
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
	 * @return Line
	 */
	public function setImageUrl($imageUrl)
	{
		$this->imageUrl = $imageUrl;

		return $this;
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
	 *
	 * @return Line
	 */
	public function setMetaData($metaData)
	{
		$this->metaData = $metaData;

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
	 * @return Line
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
	 * @return Line
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
	 * @return Line
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
	 * @return Line
	 */
	public function setTotalPrice($totalPrice)
	{
		$this->totalPrice = $totalPrice;

		return $this;
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
	 *
	 * @return Line
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
	 * @return Line
	 */
	public function setVatAmount($vatAmount)
	{
		$this->vatAmount = $vatAmount;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param string $category
	 *
	 * @return Line
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}

	public function jsonSerialize()
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
