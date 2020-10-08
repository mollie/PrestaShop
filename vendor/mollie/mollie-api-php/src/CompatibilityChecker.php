<?php

namespace MolliePrefix\Mollie\Api;

use MolliePrefix\Mollie\Api\Exceptions\IncompatiblePlatform;
class CompatibilityChecker
{
    /**
     * @var string
     */
    const MIN_PHP_VERSION = "5.6.0";
    /**
     * @throws IncompatiblePlatform
     * @return void
     */
    public function checkCompatibility()
    {
        if (!$this->satisfiesPhpVersion()) {
            throw new \MolliePrefix\Mollie\Api\Exceptions\IncompatiblePlatform("The client requires PHP version >= " . self::MIN_PHP_VERSION . ", you have " . \PHP_VERSION . ".", \MolliePrefix\Mollie\Api\Exceptions\IncompatiblePlatform::INCOMPATIBLE_PHP_VERSION);
        }
        if (!$this->satisfiesJsonExtension()) {
            throw new \MolliePrefix\Mollie\Api\Exceptions\IncompatiblePlatform("PHP extension json is not enabled. Please make sure to enable 'json' in your PHP configuration.", \MolliePrefix\Mollie\Api\Exceptions\IncompatiblePlatform::INCOMPATIBLE_JSON_EXTENSION);
        }
    }
    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesPhpVersion()
    {
        return (bool) \version_compare(\PHP_VERSION, self::MIN_PHP_VERSION, ">=");
    }
    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function satisfiesJsonExtension()
    {
        // Check by extension_loaded
        if (\function_exists('extension_loaded') && \extension_loaded('json')) {
            return \true;
        } elseif (\function_exists('json_encode')) {
            return \true;
        }
        return \false;
    }
}
