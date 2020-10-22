<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\CommandLoader;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator;
class ContainerCommandLoaderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testHas()
    {
        $loader = new \MolliePrefix\Symfony\Component\Console\CommandLoader\ContainerCommandLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        }, 'bar-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('bar');
        }]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertTrue($loader->has('foo'));
        $this->assertTrue($loader->has('bar'));
        $this->assertFalse($loader->has('baz'));
    }
    public function testGet()
    {
        $loader = new \MolliePrefix\Symfony\Component\Console\CommandLoader\ContainerCommandLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        }, 'bar-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('bar');
        }]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\Console\Command\Command::class, $loader->get('foo'));
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\Console\Command\Command::class, $loader->get('bar'));
    }
    public function testGetUnknownCommandThrows()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Console\\Exception\\CommandNotFoundException');
        (new \MolliePrefix\Symfony\Component\Console\CommandLoader\ContainerCommandLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator([]), []))->get('unknown');
    }
    public function testGetCommandNames()
    {
        $loader = new \MolliePrefix\Symfony\Component\Console\CommandLoader\ContainerCommandLoader(new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        }, 'bar-service' => function () {
            return new \MolliePrefix\Symfony\Component\Console\Command\Command('bar');
        }]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertSame(['foo', 'bar'], $loader->getNames());
    }
}
