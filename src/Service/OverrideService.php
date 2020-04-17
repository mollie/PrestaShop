<?php

namespace Mollie\Service;

use Module;
use Mollie;
use ReflectionClass;
use ReflectionMethod;
use Tools;
use Validate;

class OverrideService
{
    /**
     * @var UrlPathService
     */
    private $pathService;
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module, UrlPathService $pathService)
    {
        $this->pathService = $pathService;
        $this->module = $module;
    }

    /**
     * Find all override classes
     *
     * @return array Overridden classes
     *
     * @since 3.3.0
     */
    protected function findOverriddenClasses()
    {
        return $this->pathService->getClassesFromDir('override/classes/') + $this->pathService->getClassesFromDir('override/controllers/');
    }

    /**
     * Check if the method PaymentModule::validateOrder is overridden
     * This can cause interference with this module
     *
     * @return false|string Returns the module name if overridden, otherwise false
     *
     * @throws \ReflectionException
     * @since 3.3.0
     */
    public function checkPaymentModuleOverride()
    {
        /** @var \Mollie\Service\OverrideService $overrideService */
        foreach ($this->findOverrides() as $override) {
            if ($override['override'] === 'PaymentModule::validateOrder') {
                return $override['module_name'];
            }
        }

        return false;
    }

    /**
     * Find overrides
     *
     * @return array Overrides
     * @throws \ReflectionException
     * @since 3.3.0
     */
    private function findOverrides()
    {
        $overrides = [];

        $overriddenClasses = array_keys($this->findOverriddenClasses());

        foreach ($overriddenClasses as $overriddenClass) {
            $reflectionClass = new ReflectionClass($overriddenClass);
            $reflectionMethods = array_filter($reflectionClass->getMethods(), function ($reflectionMethod) use ($overriddenClass) {
                return $reflectionMethod->class == $overriddenClass;
            });

            if (!file_exists($reflectionClass->getFileName())) {
                continue;
            }
            $overrideFile = file($reflectionClass->getFileName());
            if (is_array($overrideFile)) {
                $overrideFile = array_diff($overrideFile, ["\n"]);
            } else {
                $overrideFile = [];
            }
            foreach ($reflectionMethods as $reflectionMethod) {
                /** @var ReflectionMethod $reflectionMethod */
                $idOverride = Tools::substr(sha1($reflectionMethod->class . '::' . $reflectionMethod->name), 0, 10);
                $overriddenMethod = [
                    'id_override' => $idOverride,
                    'override' => $reflectionMethod->class . '::' . $reflectionMethod->name,
                    'module_code' => $this->module->l('Unknown'),
                    'module_name' => $this->module->l('Unknown'),
                    'date' => $this->module->l('Unknown'),
                    'version' => $this->module->l('Unknown'),
                    'deleted' => (Tools::isSubmit('deletemodule') && Tools::getValue('id_override') === $idOverride)
                        || (Tools::isSubmit('overrideBox') && in_array($idOverride, Tools::getValue('overrideBox'))),
                ];
                if (isset($overrideFile[$reflectionMethod->getStartLine() - 5])
                    && preg_match('/module: (.*)/ism', $overrideFile[$reflectionMethod->getStartLine() - 5], $module)
                    && preg_match('/date: (.*)/ism', $overrideFile[$reflectionMethod->getStartLine() - 4], $date)
                    && preg_match('/version: ([0-9.]+)/ism', $overrideFile[$reflectionMethod->getStartLine() - 3], $version)) {
                    $overriddenMethod['module_code'] = trim($module[1]);
                    $module = Module::getInstanceByName(trim($module[1]));
                    if (Validate::isLoadedObject($module)) {
                        $overriddenMethod['module_name'] = $module->displayName;
                    }
                    $overriddenMethod['date'] = trim($date[1]);
                    $overriddenMethod['version'] = trim($version[1]);
                }
                $overrides[$idOverride] = $overriddenMethod;
            }
        }

        return $overrides;
    }
}