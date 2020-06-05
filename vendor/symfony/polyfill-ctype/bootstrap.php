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
use _PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype as p;
if (!\function_exists('ctype_alnum')) {
    function ctype_alnum($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_alnum($text);
    }
    function ctype_alpha($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_alpha($text);
    }
    function ctype_cntrl($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_cntrl($text);
    }
    function ctype_digit($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_digit($text);
    }
    function ctype_graph($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_graph($text);
    }
    function ctype_lower($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_lower($text);
    }
    function ctype_print($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_print($text);
    }
    function ctype_punct($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_punct($text);
    }
    function ctype_space($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_space($text);
    }
    function ctype_upper($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_upper($text);
    }
    function ctype_xdigit($text)
    {
        return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Ctype\Ctype::ctype_xdigit($text);
    }
}
