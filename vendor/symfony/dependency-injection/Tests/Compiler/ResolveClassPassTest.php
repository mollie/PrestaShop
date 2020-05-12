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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ChildDefinition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
class ResolveClassPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideValidClassId
     */
    public function testResolveClassFromId($serviceId)
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register($serviceId);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame($serviceId, $def->getClass());
    }
    public function provideValidClassId()
    {
        (yield ['_PhpScoper5ea00cc67502b\\Acme\\UnknownClass']);
        (yield [\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class]);
    }
    /**
     * @dataProvider provideInvalidClassId
     */
    public function testWontResolveClassFromId($serviceId)
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register($serviceId);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertNull($def->getClass());
    }
    public function provideInvalidClassId()
    {
        (yield [\stdClass::class]);
        (yield ['bar']);
        (yield ['\\DateTime']);
    }
    public function testNonFqcnChildDefinition()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $parent = $container->register('_PhpScoper5ea00cc67502b\\App\\Foo', null);
        $child = $container->setDefinition('App\\Foo.child', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ChildDefinition('_PhpScoper5ea00cc67502b\\App\\Foo'));
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame('_PhpScoper5ea00cc67502b\\App\\Foo', $parent->getClass());
        $this->assertNull($child->getClass());
    }
    public function testClassFoundChildDefinition()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $parent = $container->register('_PhpScoper5ea00cc67502b\\App\\Foo', null);
        $child = $container->setDefinition(self::class, new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ChildDefinition('_PhpScoper5ea00cc67502b\\App\\Foo'));
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame('_PhpScoper5ea00cc67502b\\App\\Foo', $parent->getClass());
        $this->assertSame(self::class, $child->getClass());
    }
    public function testAmbiguousChildDefinition()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service definition "App\\Foo\\Child" has a parent but no class, and its name looks like a FQCN. Either the class is missing or you want to inherit it from the parent service. To resolve this ambiguity, please rename this service to a non-FQCN (e.g. using dots), or create the missing class.');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('_PhpScoper5ea00cc67502b\\App\\Foo', null);
        $container->setDefinition('_PhpScoper5ea00cc67502b\\App\\Foo\\Child', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ChildDefinition('_PhpScoper5ea00cc67502b\\App\\Foo'));
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
    }
}
