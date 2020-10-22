<?php

namespace MolliePrefix;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * String helpers.
 */
class PHPUnit_Util_String
{
    /**
     * Converts a string to UTF-8 encoding.
     *
     * @param string $string
     *
     * @return string
     */
    public static function convertToUtf8($string)
    {
        return \mb_convert_encoding($string, 'UTF-8');
    }
    /**
     * Checks a string for UTF-8 encoding.
     *
     * @param string $string
     *
     * @return bool
     */
    protected static function isUtf8($string)
    {
        $length = \strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (\ord($string[$i]) < 0x80) {
                $n = 0;
            } elseif ((\ord($string[$i]) & 0xe0) == 0xc0) {
                $n = 1;
            } elseif ((\ord($string[$i]) & 0xf0) == 0xe0) {
                $n = 2;
            } elseif ((\ord($string[$i]) & 0xf0) == 0xf0) {
                $n = 3;
            } else {
                return \false;
            }
            for ($j = 0; $j < $n; $j++) {
                if (++$i == $length || (\ord($string[$i]) & 0xc0) != 0x80) {
                    return \false;
                }
            }
        }
        return \true;
    }
}
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * String helpers.
 */
\class_alias('MolliePrefix\\PHPUnit_Util_String', 'PHPUnit_Util_String', \false);
