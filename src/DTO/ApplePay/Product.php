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

namespace Mollie\DTO\ApplePay;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Product
{
    /**
     * @var int
     */
    private $productId;
    /**
     * @var int
     */
    private $productAttribute;
    /**
     * @var int
     */
    private $wantedQuantity;
    /**
     * @var int
     */
    private $customizationId;

    public function __construct(
        int $productId,
        int $productAttribute,
        int $wantedQuantity,
        int $customizationId = 0
    ) {
        $this->productId = $productId;
        $this->productAttribute = $productAttribute;
        $this->wantedQuantity = $wantedQuantity;
        $this->customizationId = $customizationId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductAttribute(): int
    {
        return $this->productAttribute;
    }

    public function getWantedQuantity(): int
    {
        return $this->wantedQuantity;
    }

    public function getCustomizationId(): int
    {
        return $this->customizationId;
    }
}
