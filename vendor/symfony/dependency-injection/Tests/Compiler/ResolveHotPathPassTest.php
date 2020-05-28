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
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveHotPathPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
class ResolveHotPathPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->addTag('container.hot_path')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('lazy')]))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('service_container'))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('', [new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('bar')]))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('baz', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('missing'));
        $container->register('lazy');
        $container->register('bar')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('buz'))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('deprec_ref_notag'));
        $container->register('baz')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('lazy'))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('lazy'));
        $container->register('buz');
        $container->register('deprec_with_tag')->setDeprecated()->addTag('container.hot_path');
        $container->register('deprec_ref_notag')->setDeprecated();
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveHotPathPass())->process($container);
        $this->assertFalse($container->getDefinition('lazy')->hasTag('container.hot_path'));
        $this->assertTrue($container->getDefinition('bar')->hasTag('container.hot_path'));
        $this->assertTrue($container->getDefinition('buz')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('baz')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('service_container')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('deprec_with_tag')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('deprec_ref_notag')->hasTag('container.hot_path'));
    }
}
