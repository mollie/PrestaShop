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
use MolliePrefix\Symfony\Component\Config\FileLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\Alias;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
/**
 * This class tests the integration of the different compiler passes.
 */
class IntegrationTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * This tests that dependencies are correctly processed.
     *
     * We're checking that:
     *
     *   * A is public, B/C are private
     *   * A -> C
     *   * B -> C
     */
    public function testProcessRemovesAndInlinesRecursively()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $a = $container->register('a', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'));
        $container->register('b', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'))->setPublic(\false);
        $c = $container->register('c', '\\stdClass')->setPublic(\false);
        $container->compile();
        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }
    public function testProcessInlinesReferencesToAliases()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $a = $container->register('a', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $container->setAlias('b', new \MolliePrefix\Symfony\Component\DependencyInjection\Alias('c', \false));
        $c = $container->register('c', '\\stdClass')->setPublic(\false);
        $container->compile();
        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasAlias('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }
    public function testProcessInlinesWhenThereAreMultipleReferencesButFromTheSameDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->register('a', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'))->addMethodCall('setC', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c')]);
        $container->register('b', '\\stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('c'))->setPublic(\false);
        $container->register('c', '\\stdClass')->setPublic(\false);
        $container->compile();
        $this->assertTrue($container->hasDefinition('a'));
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'), 'Service C was not inlined.');
    }
    public function testCanDecorateServiceSubscriber()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceSubscriberStub::class)->addTag('container.service_subscriber')->setPublic(\true);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DecoratedServiceSubscriber::class)->setDecoratedService(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceSubscriberStub::class);
        $container->compile();
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DecoratedServiceSubscriber::class, $container->get(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ServiceSubscriberStub::class));
    }
    /**
     * @dataProvider getYamlCompileTests
     */
    public function testYamlContainerCompiles($directory, $actualServiceId, $expectedServiceId, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $mainContainer = null)
    {
        // allow a container to be passed in, which might have autoconfigure settings
        $container = $mainContainer ?: new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(__DIR__ . '/../Fixtures/yaml/integration/' . $directory));
        $loader->load('main.yml');
        $container->compile();
        $actualService = $container->getDefinition($actualServiceId);
        // create a fresh ContainerBuilder, to avoid autoconfigure stuff
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(__DIR__ . '/../Fixtures/yaml/integration/' . $directory));
        $loader->load('expected.yml');
        $container->compile();
        $expectedService = $container->getDefinition($expectedServiceId);
        // reset changes, we don't care if these differ
        $actualService->setChanges([]);
        $expectedService->setChanges([]);
        $this->assertEquals($expectedService, $actualService);
    }
    public function getYamlCompileTests()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IntegrationTestStub::class);
        (yield ['autoconfigure_child_not_applied', 'child_service', 'child_service_expected', $container]);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IntegrationTestStub::class);
        (yield ['autoconfigure_parent_child', 'child_service', 'child_service_expected', $container]);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IntegrationTestStub::class)->addTag('from_autoconfigure');
        (yield ['autoconfigure_parent_child_tags', 'child_service', 'child_service_expected', $container]);
        (yield ['child_parent', 'child_service', 'child_service_expected']);
        (yield ['defaults_child_tags', 'child_service', 'child_service_expected']);
        (yield ['defaults_instanceof_importance', 'main_service', 'main_service_expected']);
        (yield ['defaults_parent_child', 'child_service', 'child_service_expected']);
        (yield ['instanceof_parent_child', 'child_service', 'child_service_expected']);
    }
}
class ServiceSubscriberStub implements \MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public static function getSubscribedServices()
    {
        return [];
    }
}
class DecoratedServiceSubscriber
{
}
class IntegrationTestStub extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IntegrationTestStubParent
{
}
class IntegrationTestStubParent
{
    public function enableSummer($enable)
    {
        // methods used in calls - added here to prevent errors for not existing
    }
    public function setSunshine($type)
    {
    }
}
