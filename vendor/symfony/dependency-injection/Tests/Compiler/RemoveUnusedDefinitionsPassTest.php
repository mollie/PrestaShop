<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\RepeatedPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
class RemoveUnusedDefinitionsPassTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setPublic(\false);
        $container->register('moo')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('moo'));
    }
    public function testProcessRemovesUnusedDefinitionsRecursively()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo')])->setPublic(\false);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertFalse($container->hasDefinition('bar'));
    }
    public function testProcessWorksWithInlinedDefinitions()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->register('bar')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(null, [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo')])]);
        $this->process($container);
        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
    }
    public function testProcessWontRemovePrivateFactory()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setFactory(['stdClass', 'getInstance'])->setPublic(\false);
        $container->register('bar', 'stdClass')->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo'), 'getInstance'])->setPublic(\false);
        $container->register('foobar')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar'));
        $this->process($container);
        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('foobar'));
    }
    public function testProcessConsiderEnvVariablesAsUsedEvenInPrivateServices()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(FOOBAR)', 'test');
        $container->register('foo')->setArguments(['%env(FOOBAR)%'])->setPublic(\false);
        $resolvePass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass();
        $resolvePass->process($container);
        $this->process($container);
        $this->assertFalse($container->hasDefinition('foo'));
        $envCounters = $container->getEnvCounters();
        $this->assertArrayHasKey('FOOBAR', $envCounters);
        $this->assertSame(1, $envCounters['FOOBAR']);
    }
    protected function process(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $repeatedPass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\RepeatedPass([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass()]);
        $repeatedPass->process($container);
    }
}
