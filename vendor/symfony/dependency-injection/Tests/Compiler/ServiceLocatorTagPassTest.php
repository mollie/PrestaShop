<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2;
require_once __DIR__ . '/../Fixtures/includes/classes.php';
class ServiceLocatorTagPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testNoServices()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid definition for service "foo": an array of references is expected as first argument when the "container.service_locator" tag is set.');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class)->addTag('container.service_locator');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
    }
    public function testInvalidServices()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid definition for service "foo": an array of references is expected as first argument when the "container.service_locator" tag is set, "string" found for key "0".');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([['dummy']])->addTag('container.service_locator');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
    }
    public function testProcessValue()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class);
        $container->register('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class);
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([[new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar'), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz'), 'some.service' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar')]])->addTag('container.service_locator');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('bar')));
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('baz')));
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \get_class($locator('some.service')));
    }
    public function testServiceWithKeyOverwritesPreviousInheritedKey()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $container->register('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class);
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([[new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar'), 'bar' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz')]])->addTag('container.service_locator');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class, \get_class($locator('bar')));
    }
    public function testInheritedKeyOverwritesPreviousServiceWithKey()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $container->register('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class);
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class)->setArguments([['bar' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz'), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar'), 16 => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz')]])->addTag('container.service_locator');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass())->process($container);
        /** @var ServiceLocator $locator */
        $locator = $container->get('foo');
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class, \get_class($locator('bar')));
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition2::class, \get_class($locator(16)));
    }
    public function testBindingsAreCopied()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo')->setBindings(['foo' => 'foo']);
        $locator = \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass::register($container, ['foo' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo')], 'foo');
        $locator = $container->getDefinition($locator);
        $locator = $container->getDefinition($locator->getFactory()[0]);
        $this->assertSame(['foo'], \array_keys($locator->getBindings()));
        $this->assertInstanceOf(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\BoundArgument::class, $locator->getBindings()['foo']);
    }
}
