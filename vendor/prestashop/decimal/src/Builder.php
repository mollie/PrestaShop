<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
/**
 * Builds Number instances
 */
class Builder
{
    /**
     * Pattern for most numbers
     */
    const NUMBER_PATTERN = "/^(?<sign>[-+])?(?<integerPart>\\d+)?(?:\\.(?<fractionalPart>\\d+)(?<exponentPart>[eE](?<exponentSign>[-+])(?<exponent>\\d+))?)?\$/";
    /**
     * Pattern for integer numbers in scientific notation (rare but supported by spec)
     */
    const INT_EXPONENTIAL_PATTERN = "/^(?<sign>[-+])?(?<integerPart>\\d+)(?<exponentPart>[eE](?<exponentSign>[-+])(?<exponent>\\d+))\$/";
    /**
     * Builds a Number from a string
     *
     * @param string $number
     *
     * @return DecimalNumber
     */
    public static function parseNumber($number)
    {
        if (!self::itLooksLikeANumber($number, $numberParts)) {
            throw new \InvalidArgumentException(\sprintf('"%s" cannot be interpreted as a number', \print_r($number, \true)));
        }
        $integerPart = '';
        if (\array_key_exists('integerPart', $numberParts)) {
            // extract the integer part and remove leading zeroes
            $integerPart = \ltrim($numberParts['integerPart'], '0');
        }
        $fractionalPart = '';
        if (\array_key_exists('fractionalPart', $numberParts)) {
            // extract the fractional part and remove trailing zeroes
            $fractionalPart = \rtrim($numberParts['fractionalPart'], '0');
        }
        $fractionalDigits = \strlen($fractionalPart);
        $coefficient = $integerPart . $fractionalPart;
        // when coefficient is '0' or a sequence of '0'
        if ('' === $coefficient) {
            $coefficient = '0';
        }
        // when the number has been provided in scientific notation
        if (\array_key_exists('exponentPart', $numberParts)) {
            $givenExponent = (int) ($numberParts['exponentSign'] . $numberParts['exponent']);
            // we simply add or subtract fractional digits from the given exponent (depending if it's positive or negative)
            $fractionalDigits -= $givenExponent;
            if ($fractionalDigits < 0) {
                // if the resulting fractional digits is negative, it means there is no fractional part anymore
                // we need to add trailing zeroes as needed
                $coefficient = \str_pad($coefficient, \strlen($coefficient) - $fractionalDigits, '0');
                // there's no fractional part anymore
                $fractionalDigits = 0;
            }
        }
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($numberParts['sign'] . $coefficient, $fractionalDigits);
    }
    /**
     * @param string $number
     * @param array $numberParts
     *
     * @return bool
     */
    private static function itLooksLikeANumber($number, &$numberParts)
    {
        return \strlen((string) $number) > 0 && (\preg_match(self::NUMBER_PATTERN, $number, $numberParts) || \preg_match(self::INT_EXPONENTIAL_PATTERN, $number, $numberParts));
    }
}
