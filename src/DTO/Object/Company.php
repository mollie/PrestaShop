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

namespace Mollie\DTO\Object;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Company implements \JsonSerializable
{
    /** @var string */
    private $vatNumber;
    /** @var string */
    private $registrationNumber;

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    /**
     * @maps vatNumber
     */
    public function setVatNumber(string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    /**
     * @maps registrationNumber
     */
    public function setRegistrationNumber(string $registrationNumber): void
    {
        $this->registrationNumber = $registrationNumber;
    }

    public function jsonSerialize()
    {
        $json = [];
        $json['vatNumber'] = $this->getVatNumber();
        $json['registrationNumber'] = $this->getRegistrationNumber();

        return array_filter($json, static function ($val) {
            return $val !== null && $val !== '';
        });
    }
}
