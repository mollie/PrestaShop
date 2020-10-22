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
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
class ClosureLoaderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSupports()
    {
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->assertTrue($loader->supports(function ($container) {
        }), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }
    public function testLoad()
    {
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder());
        $loader->load(function ($container) {
            $container->setParameter('foo', 'foo');
        });
        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a \\Closure resource');
    }
}
