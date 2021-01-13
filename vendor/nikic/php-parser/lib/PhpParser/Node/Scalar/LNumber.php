<?php

namespace MolliePrefix\PhpParser\Node\Scalar;

use MolliePrefix\PhpParser\Error;
use MolliePrefix\PhpParser\Node\Scalar;
class LNumber extends \MolliePrefix\PhpParser\Node\Scalar
{
    /* For use in "kind" attribute */
    const KIND_BIN = 2;
    const KIND_OCT = 8;
    const KIND_DEC = 10;
    const KIND_HEX = 16;
    /** @var int Number value */
    public $value;
    /**
     * Constructs an integer number scalar node.
     *
     * @param int   $value      Value of the number
     * @param array $attributes Additional attributes
     */
    public function __construct($value, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->value = $value;
    }
    public function getSubNodeNames()
    {
        return array('value');
    }
    /**
     * Constructs an LNumber node from a string number literal.
     *
     * @param string $str               String number literal (decimal, octal, hex or binary)
     * @param array  $attributes        Additional attributes
     * @param bool   $allowInvalidOctal Whether to allow invalid octal numbers (PHP 5)
     *
     * @return LNumber The constructed LNumber, including kind attribute
     */
    public static function fromString($str, array $attributes = array(), $allowInvalidOctal = \false)
    {
        if ('0' !== $str[0] || '0' === $str) {
            $attributes['kind'] = \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_DEC;
            return new \MolliePrefix\PhpParser\Node\Scalar\LNumber((int) $str, $attributes);
        }
        if ('x' === $str[1] || 'X' === $str[1]) {
            $attributes['kind'] = \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_HEX;
            return new \MolliePrefix\PhpParser\Node\Scalar\LNumber(\hexdec($str), $attributes);
        }
        if ('b' === $str[1] || 'B' === $str[1]) {
            $attributes['kind'] = \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_BIN;
            return new \MolliePrefix\PhpParser\Node\Scalar\LNumber(\bindec($str), $attributes);
        }
        if (!$allowInvalidOctal && \strpbrk($str, '89')) {
            throw new \MolliePrefix\PhpParser\Error('Invalid numeric literal', $attributes);
        }
        // use intval instead of octdec to get proper cutting behavior with malformed numbers
        $attributes['kind'] = \MolliePrefix\PhpParser\Node\Scalar\LNumber::KIND_OCT;
        return new \MolliePrefix\PhpParser\Node\Scalar\LNumber(\intval($str, 8), $attributes);
    }
}
