<?php

declare(strict_types=1);

namespace Mollie\Subscription\ServiceProvider;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Module;
use Mollie;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MollieContainer implements InteropContainerInterface
{
    /** @var Mollie */
    private $module;

    public function __construct()
    {
        /** @var Mollie $module */
        $this->module = Module::getInstanceByName('mollie');
    }

    public function get($id): object
    {
        return $this->module->getMollieContainer($id);
    }

    public function has($id): bool
    {
        return $this->module->getMollieContainer()->has($id);
    }
}
