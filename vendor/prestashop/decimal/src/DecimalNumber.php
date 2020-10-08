<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal;

use InvalidArgumentException;
use MolliePrefix\PrestaShop\Decimal\Operation\Rounding;
/**
 * Decimal number.
 *
 * Allows for arbitrary precision math operations.
 */
class DecimalNumber
{
    /**
     * Indicates if the number is negative
     * @var bool
     */
    private $isNegative = \false;
    /**
     * Integer representation of this number
     * @var string
     */
    private $coefficient = '';
    /**
     * Scientific notation exponent. For practical reasons, it's always stored as a positive value.
     * @var int
     */
    private $exponent = 0;
    /**
     * Number constructor.
     *
     * This constructor can be used in two ways:
     *
     * 1) With a number string:
     *
     * ```php
     * (string) new Number('0.123456'); // -> '0.123456'
     * ```
     *
     * 2) With an integer string as coefficient and an exponent
     *
     * ```php
     * // 123456 * 10^(-6)
     * (string) new Number('123456', 6); // -> '0.123456'
     * ```
     *
     * Note: decimal positions must always be a positive number.
     *
     * @param string $number Number or coefficient
     * @param int|null $exponent [default=null] If provided, the number can be considered as the negative
     * exponent of the scientific notation, or the number of fractional digits.
     */
    public function __construct($number, $exponent = null)
    {
        if (!\is_string($number)) {
            throw new \InvalidArgumentException(\sprintf('Invalid type - expected string, but got (%s) "%s"', \gettype($number), \print_r($number, \true)));
        }
        if (null === $exponent) {
            $decimalNumber = \MolliePrefix\PrestaShop\Decimal\Builder::parseNumber($number);
            $number = $decimalNumber->getSign() . $decimalNumber->getCoefficient();
            $exponent = $decimalNumber->getExponent();
        }
        $this->initFromScientificNotation($number, $exponent);
        if ('0' === $this->coefficient) {
            // make sure the sign is always positive for zero
            $this->isNegative = \false;
        }
    }
    /**
     * Returns the integer part of the number.
     * Note that this does NOT include the sign.
     *
     * @return string
     */
    public function getIntegerPart()
    {
        if ('0' === $this->coefficient) {
            return $this->coefficient;
        }
        if (0 === $this->exponent) {
            return $this->coefficient;
        }
        if ($this->exponent >= \strlen($this->coefficient)) {
            return '0';
        }
        return \substr($this->coefficient, 0, -$this->exponent);
    }
    /**
     * Returns the fractional part of the number.
     * Note that this does NOT include the sign.
     *
     * @return string
     */
    public function getFractionalPart()
    {
        if (0 === $this->exponent || '0' === $this->coefficient) {
            return '0';
        }
        if ($this->exponent > \strlen($this->coefficient)) {
            return \str_pad($this->coefficient, $this->exponent, '0', \STR_PAD_LEFT);
        }
        return \substr($this->coefficient, -$this->exponent);
    }
    /**
     * Returns the number of digits in the fractional part.
     *
     * @see self::getExponent() This method is an alias of getExponent().
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->getExponent();
    }
    /**
     * Returns the number's sign.
     * Note that this method will return an empty string if the number is positive!
     *
     * @return string '-' if negative, empty string if positive
     */
    public function getSign()
    {
        return $this->isNegative ? '-' : '';
    }
    /**
     * Returns the exponent of this number. For practical reasons, this exponent is always >= 0.
     *
     * This value can also be interpreted as the number of significant digits on the fractional part.
     *
     * @return int
     */
    public function getExponent()
    {
        return $this->exponent;
    }
    /**
     * Returns the raw number as stored internally. This coefficient is always an integer.
     *
     * It can be transformed to float by computing:
     * ```
     * getCoefficient() * 10^(-getExponent())
     * ```
     *
     * @return string
     */
    public function getCoefficient()
    {
        return $this->coefficient;
    }
    /**
     * Returns a string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        $output = $this->getSign() . $this->getIntegerPart();
        $fractionalPart = $this->getFractionalPart();
        if ('0' !== $fractionalPart) {
            $output .= '.' . $fractionalPart;
        }
        return $output;
    }
    /**
     * Returns the number as a string, with exactly $precision decimals
     *
     * Example:
     * ```
     * $n = new Number('123.4560');
     * (string) $n->round(1); // '123.4'
     * (string) $n->round(2); // '123.45'
     * (string) $n->round(3); // '123.456'
     * (string) $n->round(4); // '123.4560' (trailing zeroes are added)
     * (string) $n->round(5); // '123.45600' (trailing zeroes are added)
     * ```
     *
     * @param int $precision Exact number of desired decimals
     * @param string $roundingMode [default=Rounding::ROUND_TRUNCATE] Rounding algorithm
     *
     * @return string
     */
    public function toPrecision($precision, $roundingMode = \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE)
    {
        $currentPrecision = $this->getPrecision();
        if ($precision === $currentPrecision) {
            return (string) $this;
        }
        $return = $this;
        if ($precision < $currentPrecision) {
            $return = (new \MolliePrefix\PrestaShop\Decimal\Operation\Rounding())->compute($this, $precision, $roundingMode);
        }
        if ($precision > $return->getPrecision()) {
            return $return->getSign() . $return->getIntegerPart() . '.' . \str_pad($return->getFractionalPart(), $precision, '0');
        }
        return (string) $return;
    }
    /**
     * Returns the number as a string, with up to $maxDecimals significant digits.
     *
     * Example:
     * ```
     * $n = new Number('123.4560');
     * (string) $n->round(1); // '123.4'
     * (string) $n->round(2); // '123.45'
     * (string) $n->round(3); // '123.456'
     * (string) $n->round(4); // '123.456' (does not add trailing zeroes)
     * (string) $n->round(5); // '123.456' (does not add trailing zeroes)
     * ```
     *
     * @param int $maxDecimals Maximum number of decimals
     * @param string $roundingMode [default=Rounding::ROUND_TRUNCATE] Rounding algorithm
     *
     * @return string
     */
    public function round($maxDecimals, $roundingMode = \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE)
    {
        $currentPrecision = $this->getPrecision();
        if ($maxDecimals < $currentPrecision) {
            return (string) (new \MolliePrefix\PrestaShop\Decimal\Operation\Rounding())->compute($this, $maxDecimals, $roundingMode);
        }
        return (string) $this;
    }
    /**
     * Returns this number as a positive number
     *
     * @return self
     */
    public function toPositive()
    {
        if (!$this->isNegative) {
            return $this;
        }
        return $this->invert();
    }
    /**
     * Returns this number as a negative number
     *
     * @return self
     */
    public function toNegative()
    {
        if ($this->isNegative) {
            return $this;
        }
        return $this->invert();
    }
    /**
     * Returns the computed result of adding another number to this one
     *
     * @param self $addend Number to add
     *
     * @return self
     */
    public function plus(self $addend)
    {
        return (new \MolliePrefix\PrestaShop\Decimal\Operation\Addition())->compute($this, $addend);
    }
    /**
     * Returns the computed result of subtracting another number to this one
     *
     * @param self $subtrahend Number to subtract
     *
     * @return self
     */
    public function minus(self $subtrahend)
    {
        return (new \MolliePrefix\PrestaShop\Decimal\Operation\Subtraction())->compute($this, $subtrahend);
    }
    /**
     * Returns the computed result of multiplying this number with another one
     *
     * @param self $factor
     *
     * @return self
     */
    public function times(self $factor)
    {
        return (new \MolliePrefix\PrestaShop\Decimal\Operation\Multiplication())->compute($this, $factor);
    }
    /**
     * Returns the computed result of dividing this number by another one, with up to $precision number of decimals.
     *
     * A target maximum precision is required in order to handle potential infinite number of decimals
     * (e.g. 1/3 = 0.3333333...).
     *
     * If the division yields more decimal positions than the requested precision,
     * the remaining decimals are truncated, with **no rounding**.
     *
     * @param self $divisor
     * @param int $precision [optional] By default, up to Operation\Division::DEFAULT_PRECISION number of decimals.
     *
     * @return self
     *
     * @throws Exception\DivisionByZeroException
     */
    public function dividedBy(self $divisor, $precision = \MolliePrefix\PrestaShop\Decimal\Operation\Division::DEFAULT_PRECISION)
    {
        return (new \MolliePrefix\PrestaShop\Decimal\Operation\Division())->compute($this, $divisor, $precision);
    }
    /**
     * Indicates if this number equals zero
     *
     * @return bool
     */
    public function equalsZero()
    {
        return '0' == $this->getCoefficient();
    }
    /**
     * Indicates if this number is greater than the provided one
     *
     * @param self $number
     *
     * @return bool
     */
    public function isGreaterThan(self $number)
    {
        return 1 === (new \MolliePrefix\PrestaShop\Decimal\Operation\Comparison())->compare($this, $number);
    }
    /**
     * Indicates if this number is greater than zero
     *
     * @return bool
     */
    public function isGreaterThanZero()
    {
        return $this->isPositive() && !$this->equalsZero();
    }
    /**
     * Indicates if this number is greater or equal than zero
     *
     * @return bool
     */
    public function isGreaterOrEqualThanZero()
    {
        return $this->isPositive();
    }
    /**
     * Indicates if this number is greater or equal compared to the provided one
     *
     * @param self $number
     *
     * @return bool
     */
    public function isGreaterOrEqualThan(self $number)
    {
        return 0 <= (new \MolliePrefix\PrestaShop\Decimal\Operation\Comparison())->compare($this, $number);
    }
    /**
     * Indicates if this number is lower than zero
     *
     * @return bool
     */
    public function isLowerThanZero()
    {
        return $this->isNegative() && !$this->equalsZero();
    }
    /**
     * Indicates if this number is lower or equal than zero
     *
     * @return bool
     */
    public function isLowerOrEqualThanZero()
    {
        return $this->isNegative() || $this->equalsZero();
    }
    /**
     * Indicates if this number is greater than the provided one
     *
     * @param self $number
     *
     * @return bool
     */
    public function isLowerThan(self $number)
    {
        return -1 === (new \MolliePrefix\PrestaShop\Decimal\Operation\Comparison())->compare($this, $number);
    }
    /**
     * Indicates if this number is lower or equal compared to the provided one
     *
     * @param self $number
     *
     * @return bool
     */
    public function isLowerOrEqualThan(self $number)
    {
        return 0 >= (new \MolliePrefix\PrestaShop\Decimal\Operation\Comparison())->compare($this, $number);
    }
    /**
     * Indicates if this number is positive
     *
     * @return bool
     */
    public function isPositive()
    {
        return !$this->isNegative;
    }
    /**
     * Indicates if this number is negative
     *
     * @return bool
     */
    public function isNegative()
    {
        return $this->isNegative;
    }
    /**
     * Indicates if this number equals another one
     *
     * @param self $number
     *
     * @return bool
     */
    public function equals(self $number)
    {
        return $this->isNegative === $number->isNegative && $this->coefficient === $number->getCoefficient() && $this->exponent === $number->getExponent();
    }
    /**
     * Returns the additive inverse of this number (that is, N * -1).
     *
     * @return static
     */
    public function invert()
    {
        // invert sign
        $sign = $this->isNegative ? '' : '-';
        return new static($sign . $this->getCoefficient(), $this->getExponent());
    }
    /**
     * Creates a new copy of this number multiplied by 10^$exponent
     *
     * @param int $exponent
     *
     * @return static
     */
    public function toMagnitude($exponent)
    {
        return (new \MolliePrefix\PrestaShop\Decimal\Operation\MagnitudeChange())->compute($this, $exponent);
    }
    /**
     * Initializes the number using a coefficient and exponent
     *
     * @param string $coefficient
     * @param int $exponent
     */
    private function initFromScientificNotation($coefficient, $exponent)
    {
        if ($exponent < 0) {
            throw new \InvalidArgumentException(\sprintf('Invalid value for exponent. Expected a positive integer or 0, but got "%s"', $coefficient));
        }
        if (!\preg_match("/^(?<sign>[-+])?(?<integerPart>\\d+)\$/", $coefficient, $parts)) {
            throw new \InvalidArgumentException(\sprintf('"%s" cannot be interpreted as a number', $coefficient));
        }
        $this->isNegative = '-' === $parts['sign'];
        $this->exponent = (int) $exponent;
        // trim leading zeroes
        $this->coefficient = \ltrim($parts['integerPart'], '0');
        // when coefficient is '0' or a sequence of '0'
        if ('' === $this->coefficient) {
            $this->exponent = 0;
            $this->coefficient = '0';
            return;
        }
        $this->removeTrailingZeroesIfNeeded();
    }
    /**
     * Removes trailing zeroes from the fractional part and adjusts the exponent accordingly
     */
    private function removeTrailingZeroesIfNeeded()
    {
        $exponent = $this->getExponent();
        $coefficient = $this->getCoefficient();
        // trim trailing zeroes from the fractional part
        // for example 1000e-1 => 100.0
        if (0 < $exponent && '0' === \substr($coefficient, -1)) {
            $fractionalPart = $this->getFractionalPart();
            $trailingZeroesToRemove = 0;
            for ($i = $exponent - 1; $i >= 0; $i--) {
                if ('0' !== $fractionalPart[$i]) {
                    break;
                }
                $trailingZeroesToRemove++;
            }
            if ($trailingZeroesToRemove > 0) {
                $this->coefficient = \substr($coefficient, 0, -$trailingZeroesToRemove);
                $this->exponent = $exponent - $trailingZeroesToRemove;
            }
        }
    }
}
