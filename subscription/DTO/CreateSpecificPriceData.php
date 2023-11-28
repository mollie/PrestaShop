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

namespace Mollie\Subscription\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateSpecificPriceData
{
    /** @var int */
    private $productId;
    /** @var int */
    private $productAttributeId;
    /** @var float */
    private $price;
    /** @var int */
    private $customerId;
    /** @var int */
    private $shopId;
    /** @var int */
    private $shopGroupId;
    /** @var int */
    private $currencyId;

    private function __construct(
        int $productId,
        int $productAttributeId,
        float $price,
        int $customerId,
        int $shopId,
        int $shopGroupId,
        int $currencyId
    ) {
        $this->productId = $productId;
        $this->productAttributeId = $productAttributeId;
        $this->price = $price;
        $this->customerId = $customerId;
        $this->shopId = $shopId;
        $this->shopGroupId = $shopGroupId;
        $this->currencyId = $currencyId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductAttributeId(): int
    {
        return $this->productAttributeId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getShopGroupId(): int
    {
        return $this->shopGroupId;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    public static function create(
        int $productId,
        int $productAttributeId,
        float $price,
        int $customerId,
        int $shopId,
        int $shopGroupId,
        int $currencyId
    ): self {
        return new self(
            $productId,
            $productAttributeId,
            $price,
            $customerId,
            $shopId,
            $shopGroupId,
            $currencyId
        );
    }
}
