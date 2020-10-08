<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ParametersConfigurator extends \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator
{
    const FACTORY = 'parameters';
    private $container;
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $this->container = $container;
    }
    /**
     * Creates a parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public final function set($name, $value)
    {
        $this->container->setParameter($name, static::processValue($value, \true));
        return $this;
    }
    /**
     * Creates a parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public final function __invoke($name, $value)
    {
        return $this->set($name, $value);
    }
}
