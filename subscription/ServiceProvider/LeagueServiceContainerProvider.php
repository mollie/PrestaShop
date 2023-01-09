<?php

declare(strict_types=1);

namespace Mollie\Subscription\ServiceProvider;

use League\Container\Container;
use League\Container\ReflectionContainer;

class LeagueServiceContainerProvider implements ServiceContainerProviderInterface
{
    private $extendedServices = [];

    /** {@inheritDoc} */
    public function getService(string $serviceName)
    {
        $container = new Container();

        $container->delegate(new ReflectionContainer());
//        $container->delegate(new MollieContainer());
        $container->delegate(new PrestashopContainer());

        (new BaseServiceProvider($this->extendedServices))->register($container);

        return $container->get($serviceName);
    }

    public function extend(string $id, ?string $concrete = null)
    {
        $this->extendedServices[$id] = $concrete;

        return $this;
    }
}
