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

class ExpressCustomerDetailsException extends MollieException
{
    const DETAILS_MISSING = 1;
    const SHIPPING_OPTION_MISSING = 2;
    const CARRIER_NOT_FOUND = 3;
    const INVALID_FIELD = 4;
    const NOT_SAVED = 5;

    public static function detailsMissing(int $cartId): self
    {
        return new self(sprintf('Mollie has not sent the customer details for express cart %d', $cartId), self::DETAILS_MISSING);
    }

    public static function shippingOptionMissing(int $cartId): self
    {
        return new self(sprintf('Mollie has not sent the shipping option for express cart %d', $cartId), self::SHIPPING_OPTION_MISSING);
    }

    public static function carrierNotFound(string $reference): self
    {
        return new self(sprintf('No active carrier has the express shipping option reference "%s"', $reference), self::CARRIER_NOT_FOUND);
    }

    public static function invalidField(string $entity, string $field, string $reason): self
    {
        return new self(sprintf('Express %s field "%s" is invalid: %s', $entity, $field, $reason), self::INVALID_FIELD);
    }

    public static function notSaved(string $entity, int $cartId): self
    {
        return new self(sprintf('Express %s for cart %d could not be saved', $entity, $cartId), self::NOT_SAVED);
    }
}
