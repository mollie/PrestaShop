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
class CheckExceptionOnInvalidReferenceBehaviorPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b', '\\stdClass');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testProcessThrowsExceptionOnInvalidReference()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('b'));
        $this->process($container);
    }
    public function testProcessThrowsExceptionOnInvalidReferenceFromInlinedDefinition()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition();
        $def->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('a', '\\stdClass')->addArgument($def);
        $this->process($container);
    }
    public function testProcessDefinitionWithBindings()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('b')->setBindings([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('a'))]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    private function process(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass();
        $pass->process($container);
    }
}
