<?php

namespace Mollie\Application\Command;

final class UpdateApplePayShippingContact
{
    /**
     * @var int
     */
    private $productId;
    /**
     * @var int
     */
    private $productAttributeId;
    /**
     * @var int
     */
    private $customizationId;
    /**
     * @var int
     */
    private $quantityWanted;
    /**
     * @var int
     */
    private $cartId;
    /**
     * @var string
     */
    private $postalCode;
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $country;
    /**
     * @var string
     */
    private $locality;
    /**
     * @var int
     */
    private $customerId;

    public function __construct(
        int $productId,
        int $productAttributeId,
        int $customizationId,
        int $quantityWanted,
        int $cartId,
        string $postalCode,
        string $countryCode,
        string $country,
        string $locality,
        int $customerId
    )
    {
        $this->productId = $productId;
        $this->productAttributeId = $productAttributeId;
        $this->customizationId = $customizationId;
        $this->quantityWanted = $quantityWanted;
        $this->cartId = $cartId;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
        $this->country = $country;
        $this->locality = $locality;
        $this->customerId = $customerId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductAttributeId(): int
    {
        return $this->productAttributeId;
    }

    public function getCustomizationId(): int
    {
        return $this->customizationId;
    }

    public function getQuantityWanted(): int
    {
        return $this->quantityWanted;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }
}
