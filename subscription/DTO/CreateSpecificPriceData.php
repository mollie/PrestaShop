<?php

namespace Mollie\Subscription\DTO;

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

    public function __construct(
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

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getProductAttributeId(): int
    {
        return $this->productAttributeId;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @return int
     */
    public function getShopGroupId(): int
    {
        return $this->shopGroupId;
    }

    /**
     * @return int
     */
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
