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
use Mollie;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    public function getModule(): ?Mollie
    {
        /** @var ?Mollie $module */
        $module = Module::getInstanceByName('mollie') ?: null;

        return $module;
    }

    public function getModuleName(): ?string
    {
        $module = $this->getModule();

        return $module->name ?? null;
    }
}
