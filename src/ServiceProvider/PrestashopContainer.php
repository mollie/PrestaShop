<?php

declare(strict_types=1);

namespace Mollie\ServiceProvider;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrestashopContainer implements InteropContainerInterface
{
    /** @var SymfonyContainer|ContainerInterface|null */
    private $container;

    public function __construct()
    {
        $this->container = SymfonyContainer::getInstance();
    }

    public function get($id): object
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        if ($this->container === null) {
            return false;
        }

        return $this->container->has($id);
    }
}
