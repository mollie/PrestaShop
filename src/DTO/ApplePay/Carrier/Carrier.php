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

namespace Mollie\DTO\ApplePay\Carrier;

use JsonSerializable;

class Carrier implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $delay;
    /**
     * @var int
     */
    private $carrierId;
    /**
     * @var float
     */
    private $amount;

    public function __construct(
        string $name,
        string $delay,
        int $carrierId,
        float $amount
    ) {
        $this->name = $name;
        $this->delay = $delay;
        $this->carrierId = $carrierId;
        $this->amount = $amount;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDelay(): string
    {
        return $this->delay;
    }

    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function jsonSerialize()
    {
        return [
            'label' => $this->getName(),
            'detail' => $this->getDelay(),
            'amount' => $this->getAmount(),
            'identifier' => $this->getCarrierId(),
        ];
    }
}
