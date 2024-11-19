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

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopContainer implements PsrContainerInterface
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
