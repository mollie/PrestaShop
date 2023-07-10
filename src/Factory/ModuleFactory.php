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

namespace Mollie\Factory;

use Module;

class ModuleFactory
{
    public function getModuleVersion()
    {
        return Module::getInstanceByName('mollie')->version;
    }

    public function getLocalPath()
    {
        return Module::getInstanceByName('mollie')->getLocalPath();
    }

    public function getPathUri()
    {
        return Module::getInstanceByName('mollie')->getPathUri();
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return Module::getInstanceByName('mollie')->name;
    }
}
