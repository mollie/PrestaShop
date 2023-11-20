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

namespace Mollie\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This class allows to retrieve config data that can be overwritten by a .env file.
 * Otherwise it returns by default from the Config class.
 */
class Env
{
    /**
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        if (!empty($_ENV[$key])) {
            return $_ENV[$key];
        }

        return constant(Config::class . '::' . $key);
    }
}
