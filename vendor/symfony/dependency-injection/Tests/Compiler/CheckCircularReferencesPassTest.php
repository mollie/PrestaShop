<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\Compiler;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
class CheckCircularReferencesPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcessWithAliases()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->setAlias('b', 'c');
        $container->setAlias('c', 'a');
        $this->process($container);
    }
    public function testProcessWithFactory()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'stdClass')->setFactory([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'), 'getInstance']);
        $container->register('b', 'stdClass')->setFactory([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a'), 'getInstance']);
        $this->process($container);
    }
    public function testProcessDetectsIndirectCircularReference()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('c'));
        $container->register('c')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcessDetectsIndirectCircularReferenceWithFactory()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b', 'stdClass')->setFactory([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('c'), 'getInstance']);
        $container->register('c')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testDeepCircularReference()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('c'));
        $container->register('c')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $this->process($container);
    }
    public function testProcessIgnoresMethodCalls()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addMethodCall('setA', [new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a')]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testProcessIgnoresLazyServices()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setLazy(\true)->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
        // just make sure that a lazily loaded service does not trigger a CircularReferenceException
        $this->addToAssertionCount(1);
    }
    public function testProcessIgnoresIteratorArguments()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('a')]));
        $this->process($container);
        // just make sure that an IteratorArgument does not trigger a CircularReferenceException
        $this->addToAssertionCount(1);
    }
    protected function process(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $compiler = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\Compiler();
        $passConfig = $compiler->getPassConfig();
        $passConfig->setOptimizationPasses([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(\true), new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass()]);
        $passConfig->setRemovingPasses([]);
        $compiler->compile($container);
    }
}
