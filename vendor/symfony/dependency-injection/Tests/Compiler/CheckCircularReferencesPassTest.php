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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\Compiler;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class CheckCircularReferencesPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcessWithAliases()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->setAlias('b', 'c');
        $container->setAlias('c', 'a');
        $this->process($container);
    }
    public function testProcessWithFactory()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', 'stdClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'), 'getInstance']);
        $container->register('b', 'stdClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'), 'getInstance']);
        $this->process($container);
    }
    public function testProcessDetectsIndirectCircularReference()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'));
        $container->register('c')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testProcessDetectsIndirectCircularReferenceWithFactory()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b', 'stdClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'), 'getInstance']);
        $container->register('c')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
    }
    public function testDeepCircularReference()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'));
        $container->register('c')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $this->process($container);
    }
    public function testProcessIgnoresMethodCalls()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addMethodCall('setA', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a')]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testProcessIgnoresLazyServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->setLazy(\true)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'));
        $this->process($container);
        // just make sure that a lazily loaded service does not trigger a CircularReferenceException
        $this->addToAssertionCount(1);
    }
    public function testProcessIgnoresIteratorArguments()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a')]));
        $this->process($container);
        // just make sure that an IteratorArgument does not trigger a CircularReferenceException
        $this->addToAssertionCount(1);
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $compiler = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\Compiler();
        $passConfig = $compiler->getPassConfig();
        $passConfig->setOptimizationPasses([new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(\true), new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass()]);
        $passConfig->setRemovingPasses([]);
        $compiler->compile($container);
    }
}
