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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
class DefinitionErrorExceptionPassTest extends TestCase
{
    public function testThrowsException()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Things went wrong!');
        $container = new ContainerBuilder();
        $def = new Definition();
        $def->addError('Things went wrong!');
        $def->addError('Now something else!');
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new DefinitionErrorExceptionPass();
        $pass->process($container);
    }
    public function testNoExceptionThrown()
    {
        $container = new ContainerBuilder();
        $def = new Definition();
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new DefinitionErrorExceptionPass();
        $pass->process($container);
        $this->assertSame($def, $container->getDefinition('foo_service_id')->getArgument(0));
    }
}
