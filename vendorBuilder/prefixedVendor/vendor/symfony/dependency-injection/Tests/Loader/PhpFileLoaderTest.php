<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Loader;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\FileLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\Dumper\YamlDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
class PhpFileLoaderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSupports()
    {
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns false if the resource is not loadable');
        $this->assertTrue($loader->supports('with_wrong_ext.yml', 'php'), '->supports() returns true if the resource with forced type is loadable');
    }
    public function testLoad()
    {
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $loader->load(__DIR__ . '/../Fixtures/php/simple.php');
        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a PHP file resource');
    }
    public function testConfigServices()
    {
        $fixtures = \realpath(__DIR__ . '/../Fixtures');
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $loader->load($fixtures . '/config/services9.php');
        $container->compile();
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\PhpDumper($container);
        $this->assertStringEqualsFile($fixtures . '/php/services9_compiled.php', \str_replace(\str_replace('\\', '\\\\', $fixtures . \DIRECTORY_SEPARATOR . 'includes' . \DIRECTORY_SEPARATOR), '%path%', $dumper->dump()));
    }
    /**
     * @dataProvider provideConfig
     */
    public function testConfig($file)
    {
        $fixtures = \realpath(__DIR__ . '/../Fixtures');
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(), new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $loader->load($fixtures . '/config/' . $file . '.php');
        $container->compile();
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertStringEqualsFile($fixtures . '/config/' . $file . '.expected.yml', $dumper->dump());
    }
    public function provideConfig()
    {
        (yield ['basic']);
        (yield ['defaults']);
        (yield ['instanceof']);
        (yield ['prototype']);
        (yield ['child']);
        if (\PHP_VERSION_ID >= 70000) {
            (yield ['php7']);
        }
    }
    public function testAutoConfigureAndChildDefinitionNotAllowed()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The service "child_service" cannot have a "parent" and also have "autoconfigure". Try disabling autoconfiguration for the service.');
        $fixtures = \realpath(__DIR__ . '/../Fixtures');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $loader->load($fixtures . '/config/services_autoconfigure_with_parent.php');
        $container->compile();
    }
    public function testFactoryShortNotationNotAllowed()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid factory "factory:method": the `service:method` notation is not available when using PHP-based DI configuration. Use "[ref(\'factory\'), \'method\']" instead.');
        $fixtures = \realpath(__DIR__ . '/../Fixtures');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator());
        $loader->load($fixtures . '/config/factory_short_notation.php');
        $container->compile();
    }
}
