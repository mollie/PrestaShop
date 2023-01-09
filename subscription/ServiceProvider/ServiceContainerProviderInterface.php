<?php

declare(strict_types=1);

namespace Mollie\Subscription\ServiceProvider;

use League\Container\Container;

interface ServiceContainerProviderInterface
{
    /**
     * Gets service that is defined by module container.
     *
     * @param string $serviceName
     */
    public function getService(string $serviceName);

    /**
     * Extending the service. Useful for tests to dynamically change the implementations
     *
     * @param string $id
     * @param ?string $concrete - a class name
     *
     * @return mixed
     */
    public function extend(string $id, ?string $concrete = null);
}
