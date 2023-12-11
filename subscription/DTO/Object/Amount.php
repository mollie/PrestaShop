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

    public function __construct(float $value, string $currency)
    {
        Assert::greaterThanEq($value, 0, 'Amount Value cannot be negative');

        $this->value = $value;
        $this->currency = $currency;
    }

    public function toArray(): array
    {
        return [
            'value' => (string) number_format($this->value, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }

    // TODO jsonSerialize should be only used for json_encode operation. If values needs to be casted to array, use method above.
    public function jsonSerialize(): array
    {
        return [
            'value' => (string) number_format($this->value, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }
}
