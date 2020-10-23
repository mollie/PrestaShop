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
use MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
class ResolveClassPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideValidClassId
     */
    public function testResolveClassFromId($serviceId)
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register($serviceId);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame($serviceId, $def->getClass());
    }
    public function provideValidClassId()
    {
        (yield ['MolliePrefix\\Acme\\UnknownClass']);
        (yield [\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class]);
    }
    /**
     * @dataProvider provideInvalidClassId
     */
    public function testWontResolveClassFromId($serviceId)
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register($serviceId);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
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
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $parent = $container->register('MolliePrefix\\App\\Foo', null);
        $child = $container->setDefinition('App\\Foo.child', new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('MolliePrefix\\App\\Foo'));
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame('MolliePrefix\\App\\Foo', $parent->getClass());
        $this->assertNull($child->getClass());
    }
    public function testClassFoundChildDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $parent = $container->register('MolliePrefix\\App\\Foo', null);
        $child = $container->setDefinition(self::class, new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('MolliePrefix\\App\\Foo'));
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        $this->assertSame('MolliePrefix\\App\\Foo', $parent->getClass());
        $this->assertSame(self::class, $child->getClass());
    }
    public function testAmbiguousChildDefinition()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service definition "App\\Foo\\Child" has a parent but no class, and its name looks like a FQCN. Either the class is missing or you want to inherit it from the parent service. To resolve this ambiguity, please rename this service to a non-FQCN (e.g. using dots), or create the missing class.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('MolliePrefix\\App\\Foo', null);
        $container->setDefinition('MolliePrefix\\App\\Foo\\Child', new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('MolliePrefix\\App\\Foo'));
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
    }
}
