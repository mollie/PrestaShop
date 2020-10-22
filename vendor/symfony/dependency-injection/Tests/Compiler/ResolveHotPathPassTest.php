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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveHotPathPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class ResolveHotPathPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->addTag('container.hot_path')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('lazy')]))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('service_container'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('missing'));
        $container->register('lazy');
        $container->register('bar')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('buz'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('deprec_ref_notag'));
        $container->register('baz')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('lazy'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('lazy'));
        $container->register('buz');
        $container->register('deprec_with_tag')->setDeprecated()->addTag('container.hot_path');
        $container->register('deprec_ref_notag')->setDeprecated();
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveHotPathPass())->process($container);
        $this->assertFalse($container->getDefinition('lazy')->hasTag('container.hot_path'));
        $this->assertTrue($container->getDefinition('bar')->hasTag('container.hot_path'));
        $this->assertTrue($container->getDefinition('buz')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('baz')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('service_container')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('deprec_with_tag')->hasTag('container.hot_path'));
        $this->assertFalse($container->getDefinition('deprec_ref_notag')->hasTag('container.hot_path'));
    }
}
