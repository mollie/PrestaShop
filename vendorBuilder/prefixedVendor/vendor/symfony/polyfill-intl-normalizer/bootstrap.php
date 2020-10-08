<?php

namespace MolliePrefix;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MolliePrefix\Symfony\Polyfill\Intl\Normalizer as p;
if (!\function_exists('normalizer_is_normalized')) {
    function normalizer_is_normalized($s, $form = \MolliePrefix\Symfony\Polyfill\Intl\Normalizer\Normalizer::NFC)
    {
        return \MolliePrefix\Symfony\Polyfill\Intl\Normalizer\Normalizer::isNormalized($s, $form);
    }
}
if (!\function_exists('normalizer_normalize')) {
    function normalizer_normalize($s, $form = \MolliePrefix\Symfony\Polyfill\Intl\Normalizer\Normalizer::NFC)
    {
        return \MolliePrefix\Symfony\Polyfill\Intl\Normalizer\Normalizer::normalize($s, $form);
    }
}
