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
use ModuleCore;

class ModuleFactory
{
    public function getModuleVersion(): ?string
    {
        $module = $this->getModule();

        if (!$module) {
            return null;
        }

        return $module->version ?? null;
    }

    public function getLocalPath(): ?string
    {
        $module = $this->getModule();

        if (!$module) {
            return null;
        }

        return $module->getLocalPath();
    }

    public function getPathUri(): ?string
    {
        $module = $this->getModule();

        if (!$module) {
            return null;
        }

        return $module->getPathUri();
    }

    public function getModule(): ?ModuleCore
    {
        return Module::getInstanceByName('mollie') ?: null;
    }
}
