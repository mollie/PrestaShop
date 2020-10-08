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
use MolliePrefix\Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MolliePrefix\Symfony\Component\Config\Definition\ConfigurationInterface;
use MolliePrefix\Symfony\Component\Config\Resource\FileResource;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
class MergeExtensionConfigurationPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testExpressionLanguageProviderForwarding()
    {
        $tmpProviders = [];
        $extension = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface')->getMock();
        $extension->expects($this->any())->method('getXsdValidationBasePath')->willReturn(\false);
        $extension->expects($this->any())->method('getNamespace')->willReturn('http://example.org/schema/dic/foo');
        $extension->expects($this->any())->method('getAlias')->willReturn('foo');
        $extension->expects($this->once())->method('load')->willReturnCallback(function (array $config, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container) use(&$tmpProviders) {
            $tmpProviders = $container->getExpressionLanguageProviders();
        });
        $provider = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface')->getMock();
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag());
        $container->registerExtension($extension);
        $container->prependExtensionConfig('foo', ['bar' => \true]);
        $container->addExpressionLanguageProvider($provider);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
        $pass->process($container);
        $this->assertEquals([$provider], $tmpProviders);
    }
    public function testExtensionLoadGetAMergeExtensionConfigurationContainerBuilderInstance()
    {
        $extension = $this->getMockBuilder(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooExtension::class)->setMethods(['load'])->getMock();
        $extension->expects($this->once())->method('load')->with($this->isType('array'), $this->isInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder::class));
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag());
        $container->registerExtension($extension);
        $container->prependExtensionConfig('foo', []);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
        $pass->process($container);
    }
    public function testExtensionConfigurationIsTrackedByDefault()
    {
        $extension = $this->getMockBuilder(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooExtension::class)->setMethods(['getConfiguration'])->getMock();
        $extension->expects($this->exactly(2))->method('getConfiguration')->willReturn(new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooConfiguration());
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag());
        $container->registerExtension($extension);
        $container->prependExtensionConfig('foo', ['bar' => \true]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
        $pass->process($container);
        $this->assertContainsEquals(new \MolliePrefix\Symfony\Component\Config\Resource\FileResource(__FILE__), $container->getResources());
    }
    public function testOverriddenEnvsAreMerged()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerExtension(new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooExtension());
        $container->prependExtensionConfig('foo', ['bar' => '%env(FOO)%']);
        $container->prependExtensionConfig('foo', ['bar' => '%env(BAR)%', 'baz' => '%env(BAZ)%']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
        $pass->process($container);
        $this->assertSame(['BAZ', 'FOO'], \array_keys($container->getParameterBag()->getEnvPlaceholders()));
        $this->assertSame(['BAZ' => 1, 'FOO' => 0], $container->getEnvCounters());
    }
    public function testProcessedEnvsAreIncompatibleWithResolve()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Using a cast in "env(int:FOO)" is incompatible with resolution at compile time in "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\BarExtension". The logic in the extension should be moved to a compiler pass, or an env parameter with no cast should be used instead.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerExtension(new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\BarExtension());
        $container->prependExtensionConfig('bar', []);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass())->process($container);
    }
    public function testThrowingExtensionsGetMergedBag()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerExtension(new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ThrowingExtension());
        $container->prependExtensionConfig('throwing', ['bar' => '%env(FOO)%']);
        try {
            $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
            $pass->process($container);
            $this->fail('An exception should have been thrown.');
        } catch (\Exception $e) {
        }
        $this->assertSame(['FOO'], \array_keys($container->getParameterBag()->getEnvPlaceholders()));
    }
}
class FooConfiguration implements \MolliePrefix\Symfony\Component\Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\TreeBuilder();
        $rootNode = $treeBuilder->root('foo');
        $rootNode->children()->scalarNode('bar')->end()->scalarNode('baz')->end()->end();
        return $treeBuilder;
    }
}
class FooExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension
{
    public function getAlias()
    {
        return 'foo';
    }
    public function getConfiguration(array $config, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        return new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooConfiguration();
    }
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        if (isset($config['baz'])) {
            $container->getParameterBag()->get('env(BOZ)');
            $container->resolveEnvPlaceholders($config['baz']);
        }
    }
}
class BarExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension
{
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->resolveEnvPlaceholders('%env(int:FOO)%', \true);
    }
}
class ThrowingExtension extends \MolliePrefix\Symfony\Component\DependencyInjection\Extension\Extension
{
    public function getAlias()
    {
        return 'throwing';
    }
    public function getConfiguration(array $config, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        return new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\FooConfiguration();
    }
    public function load(array $configs, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        throw new \Exception();
    }
}
