<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Container;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class ServiceLocatorTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testHas()
    {
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () {
            return 'bar';
        }, 'bar' => function () {
            return 'baz';
        }, function () {
            return 'dummy';
        }]);
        $this->assertTrue($locator->has('foo'));
        $this->assertTrue($locator->has('bar'));
        $this->assertFalse($locator->has('dummy'));
    }
    public function testGet()
    {
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () {
            return 'bar';
        }, 'bar' => function () {
            return 'baz';
        }]);
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame('baz', $locator->get('bar'));
    }
    public function testGetDoesNotMemoize()
    {
        $i = 0;
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () use(&$i) {
            ++$i;
            return 'bar';
        }]);
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame(2, $i);
    }
    public function testGetThrowsOnUndefinedService()
    {
        $this->expectException('MolliePrefix\\Psr\\Container\\NotFoundExceptionInterface');
        $this->expectExceptionMessage('Service "dummy" not found: the container inside "Symfony\\Component\\DependencyInjection\\Tests\\ServiceLocatorTest" is a smaller service locator that only knows about the "foo" and "bar" services.');
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () {
            return 'bar';
        }, 'bar' => function () {
            return 'baz';
        }]);
        $locator->get('dummy');
    }
    public function testThrowsOnUndefinedInternalService()
    {
        $this->expectException('MolliePrefix\\Psr\\Container\\NotFoundExceptionInterface');
        $this->expectExceptionMessage('The service "foo" has a dependency on a non-existent service "bar". This locator only knows about the "foo" service.');
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () use(&$locator) {
            return $locator->get('bar');
        }]);
        $locator->get('foo');
    }
    public function testThrowsOnCircularReference()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for service "bar", path: "bar -> baz -> bar".');
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () use(&$locator) {
            return $locator->get('bar');
        }, 'bar' => function () use(&$locator) {
            return $locator->get('baz');
        }, 'baz' => function () use(&$locator) {
            return $locator->get('bar');
        }]);
        $locator->get('foo');
    }
    public function testThrowsInServiceSubscriber()
    {
        $this->expectException('MolliePrefix\\Psr\\Container\\NotFoundExceptionInterface');
        $this->expectExceptionMessage('Service "foo" not found: even though it exists in the app\'s container, the container inside "caller" is a smaller service locator that only knows about the "bar" service. Unless you need extra laziness, try using dependency injection instead. Otherwise, you need to declare it using "SomeServiceSubscriber::getSubscribedServices()".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('foo', new \stdClass());
        $subscriber = new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\SomeServiceSubscriber();
        $subscriber->container = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['bar' => function () {
        }]);
        $subscriber->container = $subscriber->container->withContext('caller', $container);
        $subscriber->getFoo();
    }
    public function testGetThrowsServiceNotFoundException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $this->expectExceptionMessage('Service "foo" not found: even though it exists in the app\'s container, the container inside "foo" is a smaller service locator that is empty... Try using dependency injection instead.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('foo', new \stdClass());
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator([]);
        $locator = $locator->withContext('foo', $container);
        $locator->get('foo');
    }
    public function testInvoke()
    {
        $locator = new \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator(['foo' => function () {
            return 'bar';
        }, 'bar' => function () {
            return 'baz';
        }]);
        $this->assertSame('bar', $locator('foo'));
        $this->assertSame('baz', $locator('bar'));
        $this->assertNull($locator('dummy'), '->__invoke() should return null on invalid service');
    }
}
class SomeServiceSubscriber implements \MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public $container;
    public function getFoo()
    {
        return $this->container->get('foo');
    }
    public static function getSubscribedServices()
    {
        return ['bar' => 'stdClass'];
    }
}
