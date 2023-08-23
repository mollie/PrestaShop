<?php

namespace Mollie\DTO\Object;

class Company implements \JsonSerializable
{
    /** @var string */
    private $vatNumber;
    /** @var string */
    private $registrationNumber;

    /**
     * @return string
     */
    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    /**
     * @param string $vatNumber
     *
     * @maps vatNumber
     */
    public function setVatNumber(string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    /**
     * @return string
     */
    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    /**
     * @param string $registrationNumber
     *
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
