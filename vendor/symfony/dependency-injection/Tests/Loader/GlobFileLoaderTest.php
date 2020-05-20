<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Loader;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Resource\GlobResource;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
class GlobFileLoaderTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testSupports()
    {
        $loader = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\GlobFileLoader(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder(), new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator());
        $this->assertTrue($loader->supports('any-path', 'glob'), '->supports() returns true if the resource has the glob type');
        $this->assertFalse($loader->supports('any-path'), '->supports() returns false if the resource is not of glob type');
    }
    public function testLoadAddsTheGlobResourceToTheContainer()
    {
        $loader = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Loader\GlobFileLoaderWithoutImport($container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder(), new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator());
        $loader->load(__DIR__ . '/../Fixtures/config/*');
        $this->assertEquals(new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Resource\GlobResource(__DIR__ . '/../Fixtures/config', '/*', \false), $container->getResources()[1]);
    }
}
class GlobFileLoaderWithoutImport extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\GlobFileLoader
{
    public function import($resource, $type = null, $ignoreErrors = \false, $sourceResource = null)
    {
    }
}
