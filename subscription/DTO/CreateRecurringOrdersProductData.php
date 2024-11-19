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

class CreateRecurringOrdersProductData
{
    /** @var int */
    private $productId;
    /** @var int */
    private $productAttributeId;
    /** @var int */
    private $productQuantity;
    /** @var float */
    private $unitPriceTaxExcl;

    private function __construct(
        int $productId,
        int $productAttributeId,
        int $productQuantity,
        float $unitPriceTaxExcl
    ) {
        $this->productId = $productId;
        $this->productAttributeId = $productAttributeId;
        $this->productQuantity = $productQuantity;
        $this->unitPriceTaxExcl = $unitPriceTaxExcl;
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
     * @return int
     */
    public function getProductQuantity(): int
    {
        return $this->productQuantity;
    }

    /**
     * @return float
     */
    public function getUnitPriceTaxExcl(): float
    {
        return $this->unitPriceTaxExcl;
    }

    public static function create(
        int $productId,
        int $productAttributeId,
        int $productQuantity,
        float $unitPriceTaxExcl
    ): self {
        return new self(
            $productId,
            $productAttributeId,
            $productQuantity,
            $unitPriceTaxExcl
        );
    }
}
