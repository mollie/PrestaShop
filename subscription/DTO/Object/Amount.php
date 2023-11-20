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

declare(strict_types=1);

namespace Mollie\Subscription\DTO\Object;

use JsonSerializable;
use Webmozart\Assert\Assert;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Amount implements JsonSerializable
{
    /** @var float */
    private $value;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param float $value
     * @param string $currency
     */
    public function __construct(float $value, string $currency)
    {
        Assert::greaterThanEq($value, 0, 'Amount Value cannot be negative');

        $this->value = $value;
        $this->currency = $currency;
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => (string) number_format($this->value, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }
}
