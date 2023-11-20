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

class ShippingContent
{
    /**
     * @var array
     */
    private $addressLines;
    /**
     * @var string
     */
    private $administrativeArea;
    /**
     * @var string
     */
    private $country;
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $emailAddress;
    /**
     * @var string
     */
    private $familyName;
    /**
     * @var string
     */
    private $givenName;
    /**
     * @var string
     */
    private $locality;
    /**
     * @var string
     */
    private $postalCode;

    public function __construct(
        array $addressLines,
        string $administrativeArea,
        string $country,
        string $countryCode,
        string $familyName,
        string $givenName,
        string $locality,
        string $postalCode,
        string $emailAddress = ''
    ) {
        $this->addressLines = $addressLines;
        $this->administrativeArea = $administrativeArea;
        $this->country = $country;
        $this->countryCode = $countryCode;
        $this->emailAddress = $emailAddress;
        $this->familyName = $familyName;
        $this->givenName = $givenName;
        $this->locality = $locality;
        $this->postalCode = $postalCode;
    }

    public function getAddressLines(): array
    {
        return $this->addressLines;
    }

    public function getAdministrativeArea(): string
    {
        return $this->administrativeArea;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
}
