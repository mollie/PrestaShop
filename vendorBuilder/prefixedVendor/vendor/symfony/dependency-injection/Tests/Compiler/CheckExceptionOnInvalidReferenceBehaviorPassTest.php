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
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class CheckExceptionOnInvalidReferenceBehaviorPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('b', '\\stdClass');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    public function testProcessThrowsExceptionOnInvalidReference()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $this->process($container);
    }
    public function testProcessThrowsExceptionOnInvalidReferenceFromInlinedDefinition()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
        $def->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->register('a', '\\stdClass')->addArgument($def);
        $this->process($container);
    }
    public function testProcessDefinitionWithBindings()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('b')->setBindings([new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('a'))]);
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    private function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass();
        $pass->process($container);
    }
}
