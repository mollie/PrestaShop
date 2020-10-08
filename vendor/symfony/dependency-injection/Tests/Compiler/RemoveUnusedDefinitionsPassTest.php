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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RepeatedPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class RemoveUnusedDefinitionsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setPublic(\false);
        $container->register('moo')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('moo'));
    }
    public function testProcessRemovesUnusedDefinitionsRecursively()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')])->setPublic(\false);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertFalse($container->hasDefinition('bar'));
    }
    public function testProcessWorksWithInlinedDefinitions()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Definition(null, [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')])]);
        $this->process($container);
        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
    }
    public function testProcessWontRemovePrivateFactory()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setFactory(['stdClass', 'getInstance'])->setPublic(\false);
        $container->register('bar', 'stdClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), 'getInstance'])->setPublic(\false);
        $container->register('foobar')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'));
        $this->process($container);
        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('foobar'));
    }
    public function testProcessConsiderEnvVariablesAsUsedEvenInPrivateServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(FOOBAR)', 'test');
        $container->register('foo')->setArguments(['%env(FOOBAR)%'])->setPublic(\false);
        $resolvePass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass();
        $resolvePass->process($container);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $envCounters = $container->getEnvCounters();
        $this->assertArrayHasKey('FOOBAR', $envCounters);
        $this->assertSame(1, $envCounters['FOOBAR']);
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $repeatedPass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RepeatedPass([new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass()]);
        $repeatedPass->process($container);
    }
}
