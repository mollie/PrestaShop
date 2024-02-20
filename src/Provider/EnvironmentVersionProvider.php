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

namespace Mollie\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EnvironmentVersionProvider implements EnvironmentVersionProviderInterface
{
    /**
     * @return string
     */
    public function getPrestashopVersion()
    {
        return _PS_VERSION_;
    }
}
