<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\DependencyInjection;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\DependencyInjection\ConfigCachePass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
/**
 * @group legacy
 */
class ConfigCachePassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testThatCheckersAreProcessedInPriorityOrder()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register('config_cache_factory')->addArgument(null);
        $container->register('checker_2')->addTag('config_cache.resource_checker', ['priority' => 100]);
        $container->register('checker_1')->addTag('config_cache.resource_checker', ['priority' => 200]);
        $container->register('checker_3')->addTag('config_cache.resource_checker');
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\DependencyInjection\ConfigCachePass();
        $pass->process($container);
        $expected = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('checker_1'), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('checker_2'), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('checker_3')]);
        $this->assertEquals($expected, $definition->getArgument(0));
    }
    public function testThatCheckersCanBeMissing()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definitionsBefore = \count($container->getDefinitions());
        $aliasesBefore = \count($container->getAliases());
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\DependencyInjection\ConfigCachePass();
        $pass->process($container);
        // the container is untouched (i.e. no new definitions or aliases)
        $this->assertCount($definitionsBefore, $container->getDefinitions());
        $this->assertCount($aliasesBefore, $container->getAliases());
    }
}
