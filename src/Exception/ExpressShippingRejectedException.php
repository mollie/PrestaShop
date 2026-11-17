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

namespace Mollie\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The shop cannot ship the cart to the address the wallet sent. The type is the one Mollie's
 * shipping callback contract expects in its 422 answer; the message is for logs only.
 */
class ExpressShippingRejectedException extends MollieException
{
    const UNSUPPORTED_ADDRESS = 'unsupported-address';
    const UNSUPPORTED_COUNTRY = 'unsupported-country';
    const UNSUPPORTED_POSTAL_CODE = 'unsupported-postal-code';

    /** @var string */
    private $type = self::UNSUPPORTED_ADDRESS;

    public static function unsupportedAddress(string $detail): self
    {
        return self::withType(self::UNSUPPORTED_ADDRESS, $detail);
    }

    public static function unsupportedCountry(string $detail): self
    {
        return self::withType(self::UNSUPPORTED_COUNTRY, $detail);
    }

    public static function unsupportedPostalCode(string $detail): self
    {
        return self::withType(self::UNSUPPORTED_POSTAL_CODE, $detail);
    }

    public function getType(): string
    {
        return $this->type;
    }

    private static function withType(string $type, string $detail): self
    {
        $exception = new self($detail);
        $exception->type = $type;

        return $exception;
    }
}
