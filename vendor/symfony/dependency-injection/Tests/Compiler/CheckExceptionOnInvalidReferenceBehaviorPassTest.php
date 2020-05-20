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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
class CheckExceptionOnInvalidReferenceBehaviorPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new Reference('b'));
        $container->register('b', '\\stdClass');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testProcessThrowsExceptionOnInvalidReference()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new Reference('b'));
        $this->process($container);
    }
    public function testProcessThrowsExceptionOnInvalidReferenceFromInlinedDefinition()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new ContainerBuilder();
        $def = new Definition();
        $def->addArgument(new Reference('b'));
        $container->register('a', '\\stdClass')->addArgument($def);
        $this->process($container);
    }
    public function testProcessDefinitionWithBindings()
    {
        $container = new ContainerBuilder();
        $container->register('b')->setBindings([new BoundArgument(new Reference('a'))]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    private function process(ContainerBuilder $container)
    {
        $pass = new CheckExceptionOnInvalidReferenceBehaviorPass();
        $pass->process($container);
    }
}
