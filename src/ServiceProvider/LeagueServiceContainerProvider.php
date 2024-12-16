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

declare(strict_types=1);

namespace Mollie\ServiceProvider;

use League\Container\Container;
use League\Container\ReflectionContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LeagueServiceContainerProvider implements ServiceContainerProviderInterface
{
    private $extendedServices = [];

    /** {@inheritDoc} */
    public function getService(string $serviceName)
    {
        $container = new Container();

        $container->delegate(new ReflectionContainer());
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
