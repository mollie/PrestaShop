<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\Extension;
/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ExtensionCompilerPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    private $container;
    private $pass;
    protected function setUp()
    {
        $this->container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass();
    }
    public function testProcess()
    {
        $extension1 = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CompilerPassExtension('extension1');
        $extension2 = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension('extension2');
        $extension3 = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension('extension3');
        $extension4 = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CompilerPassExtension('extension4');
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
class DummyExtension extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Extension\Extension
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
    public function load(array $configs, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
    }
    public function process(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->register($this->alias);
    }
}
class CompilerPassExtension extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DummyExtension implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
}
