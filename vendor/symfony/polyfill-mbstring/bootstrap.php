<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use _PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring as p;
if (!\defined('MB_CASE_UPPER')) {
    \define('MB_CASE_UPPER', 0);
    \define('MB_CASE_LOWER', 1);
    \define('MB_CASE_TITLE', 2);
}
if (!\function_exists('mb_strlen')) {
    function mb_convert_encoding($s, $to, $from = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_encoding($s, $to, $from);
    }
    function mb_decode_mimeheader($s)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_decode_mimeheader($s);
    }
    function mb_encode_mimeheader($s, $charset = null, $transferEnc = null, $lf = null, $indent = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_encode_mimeheader($s, $charset, $transferEnc, $lf, $indent);
    }
    function mb_decode_numericentity($s, $convmap, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_decode_numericentity($s, $convmap, $enc);
    }
    function mb_encode_numericentity($s, $convmap, $enc = null, $is_hex = \false)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_encode_numericentity($s, $convmap, $enc, $is_hex);
    }
    function mb_convert_case($s, $mode, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_case($s, $mode, $enc);
    }
    function mb_internal_encoding($enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_internal_encoding($enc);
    }
    function mb_language($lang = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_language($lang);
    }
    function mb_list_encodings()
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_list_encodings();
    }
    function mb_encoding_aliases($encoding)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_encoding_aliases($encoding);
    }
    function mb_check_encoding($var = null, $encoding = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_check_encoding($var, $encoding);
    }
    function mb_detect_encoding($str, $encodingList = null, $strict = \false)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_detect_encoding($str, $encodingList, $strict);
    }
    function mb_detect_order($encodingList = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_detect_order($encodingList);
    }
    function mb_parse_str($s, &$result = array())
    {
        \parse_str($s, $result);
    }
    function mb_strlen($s, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strlen($s, $enc);
    }
    function mb_strpos($s, $needle, $offset = 0, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strpos($s, $needle, $offset, $enc);
    }
    function mb_strtolower($s, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strtolower($s, $enc);
    }
    function mb_strtoupper($s, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strtoupper($s, $enc);
    }
    function mb_substitute_character($char = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_substitute_character($char);
    }
    function mb_substr($s, $start, $length = 2147483647, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_substr($s, $start, $length, $enc);
    }
    function mb_stripos($s, $needle, $offset = 0, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_stripos($s, $needle, $offset, $enc);
    }
    function mb_stristr($s, $needle, $part = \false, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_stristr($s, $needle, $part, $enc);
    }
    function mb_strrchr($s, $needle, $part = \false, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strrchr($s, $needle, $part, $enc);
    }
    function mb_strrichr($s, $needle, $part = \false, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strrichr($s, $needle, $part, $enc);
    }
    function mb_strripos($s, $needle, $offset = 0, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strripos($s, $needle, $offset, $enc);
    }
    function mb_strrpos($s, $needle, $offset = 0, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strrpos($s, $needle, $offset, $enc);
    }
    function mb_strstr($s, $needle, $part = \false, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strstr($s, $needle, $part, $enc);
    }
    function mb_get_info($type = 'all')
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_get_info($type);
    }
    function mb_http_output($enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_http_output($enc);
    }
    function mb_strwidth($s, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_strwidth($s, $enc);
    }
    function mb_substr_count($haystack, $needle, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_substr_count($haystack, $needle, $enc);
    }
    function mb_output_handler($contents, $status)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_output_handler($contents, $status);
    }
    function mb_http_input($type = '')
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_http_input($type);
    }
    function mb_convert_variables($toEncoding, $fromEncoding, &$a = null, &$b = null, &$c = null, &$d = null, &$e = null, &$f = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_convert_variables($toEncoding, $fromEncoding, $a, $b, $c, $d, $e, $f);
    }
}
if (!\function_exists('_PhpScoper5ea00cc67502b\\mb_chr')) {
    function mb_ord($s, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_ord($s, $enc);
    }
    function mb_chr($code, $enc = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_chr($code, $enc);
    }
    function mb_scrub($s, $enc = null)
    {
        $enc = null === $enc ? \mb_internal_encoding() : $enc;
        return \mb_convert_encoding($s, $enc, $enc);
    }
}
if (!\function_exists('_PhpScoper5ea00cc67502b\\mb_str_split')) {
    function mb_str_split($string, $split_length = 1, $encoding = null)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Mbstring\Mbstring::mb_str_split($string, $split_length, $encoding);
    }
}
