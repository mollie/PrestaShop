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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
class DefinitionErrorExceptionPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testThrowsException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Things went wrong!');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
        $def->addError('Things went wrong!');
        $def->addError('Now something else!');
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass();
        $pass->process($container);
    }
    public function testNoExceptionThrown()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
        $container->register('foo_service_id')->setArguments([$def]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass();
        $pass->process($container);
        $this->assertSame($def, $container->getDefinition('foo_service_id')->getArgument(0));
    }
}
