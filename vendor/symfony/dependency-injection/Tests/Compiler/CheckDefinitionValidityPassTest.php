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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckDefinitionValidityPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
class CheckDefinitionValidityPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcessDetectsSyntheticNonPublicDefinitions()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setSynthetic(\true)->setPublic(\false);
        $this->process($container);
    }
    public function testProcessDetectsNonSyntheticNonAbstractDefinitionWithoutClass()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setSynthetic(\false)->setAbstract(\false);
        $this->process($container);
    }
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'class');
        $container->register('b', 'class')->setSynthetic(\true)->setPublic(\true);
        $container->register('c', 'class')->setAbstract(\true);
        $container->register('d', 'class')->setSynthetic(\true);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testValidTags()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'class')->addTag('foo', ['bar' => 'baz']);
        $container->register('b', 'class')->addTag('foo', ['bar' => null]);
        $container->register('c', 'class')->addTag('foo', ['bar' => 1]);
        $container->register('d', 'class')->addTag('foo', ['bar' => 1.1]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testInvalidTags()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'class')->addTag('foo', ['bar' => ['baz' => 'baz']]);
        $this->process($container);
    }
    public function testDynamicPublicServiceName()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\EnvParameterException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $env = $container->getParameterBag()->get('env(BAR)');
        $container->register("foo.{$env}", 'class')->setPublic(\true);
        $this->process($container);
    }
    public function testDynamicPublicAliasName()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\EnvParameterException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $env = $container->getParameterBag()->get('env(BAR)');
        $container->setAlias("foo.{$env}", 'class')->setPublic(\true);
        $this->process($container);
    }
    public function testDynamicPrivateName()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $env = $container->getParameterBag()->get('env(BAR)');
        $container->register("foo.{$env}", 'class');
        $container->setAlias("bar.{$env}", 'class');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckDefinitionValidityPass();
        $pass->process($container);
    }
}
