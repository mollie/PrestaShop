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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
class ClosureLoaderTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testSupports()
    {
        $loader = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\ClosureLoader(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->assertTrue($loader->supports(function ($container) {
        }), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }
    public function testLoad()
    {
        $loader = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder());
        $loader->load(function ($container) {
            $container->setParameter('foo', 'foo');
        });
        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a \\Closure resource');
    }
}
