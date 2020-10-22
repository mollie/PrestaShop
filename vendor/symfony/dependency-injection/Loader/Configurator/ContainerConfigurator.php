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

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Expression;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ContainerConfigurator extends \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator
{
    const FACTORY = 'container';
    private $container;
    private $loader;
    private $instanceof;
    private $path;
    private $file;
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container, \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader $loader, array &$instanceof, $path, $file)
    {
        $this->container = $container;
        $this->loader = $loader;
        $this->instanceof =& $instanceof;
        $this->path = $path;
        $this->file = $file;
    }
    public final function extension($namespace, array $config)
    {
        if (!$this->container->hasExtension($namespace)) {
            $extensions = \array_filter(\array_map(function ($ext) {
                return $ext->getAlias();
            }, $this->container->getExtensions()));
            throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('There is no extension able to load the configuration for "%s" (in "%s"). Looked for namespace "%s", found "%s".', $namespace, $this->file, $namespace, $extensions ? \implode('", "', $extensions) : 'none'));
        }
        $this->container->loadFromExtension($namespace, static::processValue($config));
    }
    public final function import($resource, $type = null, $ignoreErrors = \false)
    {
        $this->loader->setCurrentDir(\dirname($this->path));
        $this->loader->import($resource, $type, $ignoreErrors, $this->file);
    }
    /**
     * @return ParametersConfigurator
     */
    public final function parameters()
    {
        return new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ParametersConfigurator($this->container);
    }
    /**
     * @return ServicesConfigurator
     */
    public final function services()
    {
        return new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator($this->container, $this->loader, $this->instanceof);
    }
}
/**
 * Creates a service reference.
 *
 * @param string $id
 *
 * @return ReferenceConfigurator
 */
function ref($id)
{
    return new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator($id);
}
/**
 * Creates an inline service.
 *
 * @param string|null $class
 *
 * @return InlineServiceConfigurator
 */
function inline($class = null)
{
    return new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\InlineServiceConfigurator(new \MolliePrefix\Symfony\Component\DependencyInjection\Definition($class));
}
/**
 * Creates a lazy iterator.
 *
 * @param ReferenceConfigurator[] $values
 *
 * @return IteratorArgument
 */
function iterator(array $values)
{
    return new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument(\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator::processValue($values, \true));
}
/**
 * Creates a lazy iterator by tag name.
 *
 * @param string $tag
 *
 * @return TaggedIteratorArgument
 */
function tagged($tag)
{
    return new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument($tag);
}
/**
 * Creates an expression.
 *
 * @param string $expression an expression
 *
 * @return Expression
 */
function expr($expression)
{
    return new \MolliePrefix\Symfony\Component\ExpressionLanguage\Expression($expression);
}
