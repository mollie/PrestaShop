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
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2;
require_once __DIR__ . '/../Fixtures/includes/classes.php';
class ServiceLocatorTagPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testNoServices()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid definition for service "foo": an array of references is expected as first argument when the "container.service_locator" tag is set.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->addTag('container.service_locator');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
    }
    public function testInvalidServices()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid definition for service "foo": an array of references is expected as first argument when the "container.service_locator" tag is set, "string" found for key "0".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([['dummy']])->addTag('container.service_locator');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
    }
    public function testProcessValue()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class);
        $container->register('baz', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz'), 'some.service' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]])->addTag('container.service_locator');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('bar')));
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('baz')));
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('some.service')));
    }
    public function testServiceWithKeyOverwritesPreviousInheritedKey()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $container->register('baz', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), 'bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz')]])->addTag('container.service_locator');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class, \get_class($locator('bar')));
    }
    public function testInheritedKeyOverwritesPreviousServiceWithKey()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $container->register('baz', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([['bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), 16 => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz')]])->addTag('container.service_locator');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class, \get_class($locator('bar')));
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class, \get_class($locator(16)));
    }
    public function testBindingsAreCopied()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setBindings(['foo' => 'foo']);
        $locator = \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass::register($container, ['foo' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')], 'foo');
        $locator = $container->getDefinition($locator);
        $locator = $container->getDefinition($locator->getFactory()[0]);
        $this->assertSame(['foo'], \array_keys($locator->getBindings()));
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument::class, $locator->getBindings()['foo']);
    }
}
