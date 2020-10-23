<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\DependencyInjection;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\DependencyInjection\ConfigCachePass;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
/**
 * @group legacy
 */
class ConfigCachePassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testThatCheckersAreProcessedInPriorityOrder()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register('config_cache_factory')->addArgument(null);
        $container->register('checker_2')->addTag('config_cache.resource_checker', ['priority' => 100]);
        $container->register('checker_1')->addTag('config_cache.resource_checker', ['priority' => 200]);
        $container->register('checker_3')->addTag('config_cache.resource_checker');
        $pass = new \MolliePrefix\Symfony\Component\Config\DependencyInjection\ConfigCachePass();
        $pass->process($container);
        $expected = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('checker_1'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('checker_2'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('checker_3')]);
        $this->assertEquals($expected, $definition->getArgument(0));
    }
    public function testThatCheckersCanBeMissing()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definitionsBefore = \count($container->getDefinitions());
        $aliasesBefore = \count($container->getAliases());
        $pass = new \MolliePrefix\Symfony\Component\Config\DependencyInjection\ConfigCachePass();
        $pass->process($container);
        // the container is untouched (i.e. no new definitions or aliases)
        $this->assertCount($definitionsBefore, $container->getDefinitions());
        $this->assertCount($aliasesBefore, $container->getAliases());
    }
}
