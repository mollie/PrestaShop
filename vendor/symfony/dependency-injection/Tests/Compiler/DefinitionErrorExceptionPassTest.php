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
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
class DefinitionErrorExceptionPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testThrowsException()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Things went wrong!');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition();
        $def->addError('Things went wrong!');
        $def->addError('Now something else!');
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass();
        $pass->process($container);
    }
    public function testNoExceptionThrown()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition();
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass();
        $pass->process($container);
        $this->assertSame($def, $container->getDefinition('foo_service_id')->getArgument(0));
    }
}
