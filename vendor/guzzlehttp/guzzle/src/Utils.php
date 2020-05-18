<?php

namespace _PhpScoper5ea00cc67502b\GuzzleHttp;

use _PhpScoper5ea00cc67502b\GuzzleHttp\Exception\InvalidArgumentException;
use _PhpScoper5ea00cc67502b\Psr\Http\Message\UriInterface;
use function array_filter;
use function array_keys;
use function constant;
use function defined;
use function function_exists;
use function get_defined_constants;
use function idn_to_ascii;
use function implode;
use function microtime;
use function substr;
use const INTL_IDNA_VARIANT_UTS46;

final class Utils
{
    /**
     * Wrapper for the hrtime() or microtime() functions
     * (depending on the PHP version, one of the two is used)
     *
     * @return float|mixed UNIX timestamp
     *
     * @internal
     */
    public static function currentTime()
    {
        return function_exists('_PhpScoper5ea00cc67502b\\hrtime') ? hrtime(true) / 1000000000.0 : microtime(true);
    }
    /**
     * @param int $options
     *
     * @return UriInterface
     * @throws InvalidArgumentException
     *
     * @internal
     */
    public static function idnUriConvert(UriInterface $uri, $options = 0)
    {
        if ($uri->getHost()) {
            $idnaVariant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
            $asciiHost = $idnaVariant === 0 ? idn_to_ascii($uri->getHost(), $options) : idn_to_ascii($uri->getHost(), $options, $idnaVariant, $info);
            if ($asciiHost === false) {
                $errorBitSet = isset($info['errors']) ? $info['errors'] : 0;
                $errorConstants = array_filter(array_keys(get_defined_constants()), function ($name) {
                    return substr($name, 0, 11) === 'IDNA_ERROR_';
                });
                $errors = [];
                foreach ($errorConstants as $errorConstant) {
                    if ($errorBitSet & constant($errorConstant)) {
                        $errors[] = $errorConstant;
                    }
                }
                $errorMessage = 'IDN conversion failed';
                if ($errors) {
                    $errorMessage .= ' (errors: ' . implode(', ', $errors) . ')';
                }
                throw new InvalidArgumentException($errorMessage);
            } else {
                if ($uri->getHost() !== $asciiHost) {
                    // Replace URI only if the ASCII version is different
                    $uri = $uri->withHost($asciiHost);
                }
            }
        }
        return $uri;
    }
}
