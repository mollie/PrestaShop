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
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RepeatedPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class InlineServiceDefinitionsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('inlinable.service')->setPublic(\false);
        $container->register('service')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('inlinable.service')]);
        $this->process($container);
        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Definition', $arguments[0]);
        $this->assertSame($container->getDefinition('inlinable.service'), $arguments[0]);
    }
    public function testProcessDoesNotInlinesWhenAliasedServiceIsShared()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false);
        $container->setAlias('moo', 'foo');
        $container->register('service')->setArguments([$ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $this->process($container);
        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($ref, $arguments[0]);
    }
    public function testProcessDoesInlineNonSharedService()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setShared(\false);
        $container->register('bar')->setPublic(\false)->setShared(\false);
        $container->setAlias('moo', 'bar');
        $container->register('service')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), $ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('moo'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertEquals($container->getDefinition('foo'), $arguments[0]);
        $this->assertNotSame($container->getDefinition('foo'), $arguments[0]);
        $this->assertSame($ref, $arguments[1]);
        $this->assertEquals($container->getDefinition('bar'), $arguments[2]);
        $this->assertNotSame($container->getDefinition('bar'), $arguments[2]);
    }
    public function testProcessDoesNotInlineMixedServicesLoop()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'))->setShared(\false);
        $container->register('bar')->setPublic(\false)->addMethodCall('setFoo', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $this->process($container);
        $this->assertEquals(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), $container->getDefinition('foo')->getArgument(0));
    }
    public function testProcessThrowsOnNonSharedLoops()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for service "bar", path: "bar -> foo -> bar".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'))->setShared(\false);
        $container->register('bar')->setShared(\false)->addMethodCall('setFoo', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $this->process($container);
    }
    public function testProcessNestedNonSharedServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar1'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar2'));
        $container->register('bar1')->setShared(\false)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz'));
        $container->register('bar2')->setShared(\false)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz'));
        $container->register('baz')->setShared(\false);
        $this->process($container);
        $baz1 = $container->getDefinition('foo')->getArgument(0)->getArgument(0);
        $baz2 = $container->getDefinition('foo')->getArgument(1)->getArgument(0);
        $this->assertEquals($container->getDefinition('baz'), $baz1);
        $this->assertEquals($container->getDefinition('baz'), $baz2);
        $this->assertNotSame($baz1, $baz2);
    }
    public function testProcessInlinesIfMultipleReferencesButAllFromTheSameDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $a = $container->register('a')->setPublic(\false);
        $b = $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Definition(null, [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a')]));
        $this->process($container);
        $arguments = $b->getArguments();
        $this->assertSame($a, $arguments[0]);
        $inlinedArguments = $arguments[1]->getArguments();
        $this->assertSame($a, $inlinedArguments[0]);
    }
    public function testProcessInlinesPrivateFactoryReference()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setPublic(\false);
        $b = $container->register('b')->setPublic(\false)->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'), 'a']);
        $container->register('foo')->setArguments([$ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b')]);
        $this->process($container);
        $inlinedArguments = $container->getDefinition('foo')->getArguments();
        $this->assertSame($b, $inlinedArguments[0]);
    }
    public function testProcessDoesNotInlinePrivateFactoryIfReferencedMultipleTimesWithinTheSameDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a');
        $container->register('b')->setPublic(\false)->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'), 'a']);
        $container->register('foo')->setArguments([$ref1 = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), $ref2 = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b')]);
        $this->process($container);
        $args = $container->getDefinition('foo')->getArguments();
        $this->assertSame($ref1, $args[0]);
        $this->assertSame($ref2, $args[1]);
    }
    public function testProcessDoesNotInlineReferenceWhenUsedByInlineFactory()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a');
        $container->register('b')->setPublic(\false)->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'), 'a']);
        $inlineFactory = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
        $inlineFactory->setPublic(\false);
        $inlineFactory->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), 'b']);
        $container->register('foo')->setArguments([$ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), $inlineFactory]);
        $this->process($container);
        $args = $container->getDefinition('foo')->getArguments();
        $this->assertSame($ref, $args[0]);
    }
    public function testProcessDoesNotInlineWhenServiceIsPrivateButLazy()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false)->setLazy(\true);
        $container->register('service')->setArguments([$ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $this->process($container);
        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($ref, $arguments[0]);
    }
    public function testProcessDoesNotInlineWhenServiceReferencesItself()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setPublic(\false)->addMethodCall('foo', [$ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $this->process($container);
        $calls = $container->getDefinition('foo')->getMethodCalls();
        $this->assertSame($ref, $calls[0][1][0]);
    }
    public function testProcessDoesNotSetLazyArgumentValuesAfterInlining()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('inline')->setShared(\false);
        $container->register('service-closure')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('inline'))]);
        $container->register('iterator')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('inline')])]);
        $this->process($container);
        $values = $container->getDefinition('service-closure')->getArgument(0)->getValues();
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Reference::class, $values[0]);
        $this->assertSame('inline', (string) $values[0]);
        $values = $container->getDefinition('iterator')->getArgument(0)->getValues();
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Reference::class, $values[0]);
        $this->assertSame('inline', (string) $values[0]);
    }
    /**
     * @group legacy
     */
    public function testGetInlinedServiceIdData()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('inlinable.service')->setPublic(\false);
        $container->register('non_inlinable.service')->setPublic(\true);
        $container->register('other_service')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('inlinable.service')]);
        $inlinePass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass();
        $repeatedPass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RepeatedPass([new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), $inlinePass]);
        $repeatedPass->process($container);
        $this->assertEquals(['inlinable.service' => ['other_service']], $inlinePass->getInlinedServiceIds());
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $repeatedPass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RepeatedPass([new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass()]);
        $repeatedPass->process($container);
    }
}
