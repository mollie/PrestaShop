<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension;
/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ExtensionCompilerPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    private $container;
    private $pass;
    protected function setUp()
    {
        $this->container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass();
    }
    public function testProcess()
    {
        $extension1 = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CompilerPassExtension('extension1');
        $extension2 = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension('extension2');
        $extension3 = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension('extension3');
        $extension4 = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CompilerPassExtension('extension4');
        $this->container->registerExtension($extension1);
        $this->container->registerExtension($extension2);
        $this->container->registerExtension($extension3);
        $this->container->registerExtension($extension4);
        $this->pass->process($this->container);
        $this->assertTrue($this->container->hasDefinition('extension1'));
        $this->assertFalse($this->container->hasDefinition('extension2'));
        $this->assertFalse($this->container->hasDefinition('extension3'));
        $this->assertTrue($this->container->hasDefinition('extension4'));
    }
}
class DummyExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension
{
    private $alias;
    public function __construct($alias)
    {
        $this->alias = $alias;
    }
    public function getAlias()
    {
        return $this->alias;
    }
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
    public function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->register($this->alias);
    }
}
class CompilerPassExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension implements \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
}
