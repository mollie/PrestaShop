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
use MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
class ResolveInstanceofConditionalsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo', self::class)->addTag('tag')->setAutowired(\true)->setChanges([]);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->setProperty('foo', 'bar')->addTag('baz', ['attr' => 123])]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        $parent = 'instanceof.' . parent::class . '.0.foo';
        $def = $container->getDefinition('foo');
        $this->assertEmpty($def->getInstanceofConditionals());
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition::class, $def);
        $this->assertTrue($def->isAutowired());
        $this->assertSame($parent, $def->getParent());
        $this->assertSame(['tag' => [[]], 'baz' => [['attr' => 123]]], $def->getTags());
        $parent = $container->getDefinition($parent);
        $this->assertSame(['foo' => 'bar'], $parent->getProperties());
        $this->assertSame([], $parent->getTags());
    }
    public function testProcessInheritance()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('parent', parent::class)->addMethodCall('foo', ['foo']);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addMethodCall('foo', ['bar'])]);
        $def = (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('parent'))->setClass(self::class);
        $container->setDefinition('child', $def);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass())->process($container);
        $expected = [['foo', ['bar']], ['foo', ['foo']]];
        $this->assertSame($expected, $container->getDefinition('parent')->getMethodCalls());
        $this->assertSame($expected, $container->getDefinition('child')->getMethodCalls());
    }
    public function testProcessDoesReplaceShared()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo', 'stdClass');
        $def->setInstanceofConditionals(['stdClass' => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->setShared(\false)]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        $def = $container->getDefinition('foo');
        $this->assertFalse($def->isShared());
    }
    public function testProcessHandlesMultipleInheritance()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo', self::class)->setShared(\true);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->setLazy(\true)->setShared(\false), self::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->setAutowired(\true)]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass())->process($container);
        $def = $container->getDefinition('foo');
        $this->assertTrue($def->isAutowired());
        $this->assertTrue($def->isLazy());
        $this->assertTrue($def->isShared());
    }
    public function testProcessUsesAutoconfiguredInstanceof()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('normal_service', self::class);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addTag('local_instanceof_tag')->setFactory('locally_set_factory')]);
        $def->setAutoconfigured(\true);
        $container->registerForAutoconfiguration(parent::class)->addTag('autoconfigured_tag')->setAutowired(\true)->setFactory('autoconfigured_factory');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass())->process($container);
        $def = $container->getDefinition('normal_service');
        // autowired thanks to the autoconfigured instanceof
        $this->assertTrue($def->isAutowired());
        // factory from the specific instanceof overrides global one
        $this->assertEquals('locally_set_factory', $def->getFactory());
        // tags are merged, the locally set one is first
        $this->assertSame(['local_instanceof_tag' => [[]], 'autoconfigured_tag' => [[]]], $def->getTags());
    }
    public function testAutoconfigureInstanceofDoesNotDuplicateTags()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('normal_service', self::class);
        $def->addTag('duplicated_tag')->addTag('duplicated_tag', ['and_attributes' => 1]);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addTag('duplicated_tag')]);
        $def->setAutoconfigured(\true);
        $container->registerForAutoconfiguration(parent::class)->addTag('duplicated_tag', ['and_attributes' => 1]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass())->process($container);
        $def = $container->getDefinition('normal_service');
        $this->assertSame(['duplicated_tag' => [[], ['and_attributes' => 1]]], $def->getTags());
    }
    public function testProcessDoesNotUseAutoconfiguredInstanceofIfNotEnabled()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('normal_service', self::class);
        $def->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addTag('foo_tag')]);
        $container->registerForAutoconfiguration(parent::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass())->process($container);
        $def = $container->getDefinition('normal_service');
        $this->assertFalse($def->isAutowired());
    }
    public function testBadInterfaceThrowsException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('"App\\FakeInterface" is set as an "instanceof" conditional, but it does not exist.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('normal_service', self::class);
        $def->setInstanceofConditionals(['MolliePrefix\\App\\FakeInterface' => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addTag('foo_tag')]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
    }
    public function testBadInterfaceForAutomaticInstanceofIsOk()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('normal_service', self::class)->setAutoconfigured(\true);
        $container->registerForAutoconfiguration('MolliePrefix\\App\\FakeInterface')->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        $this->assertTrue($container->hasDefinition('normal_service'));
    }
    public function testProcessThrowsExceptionForAutoconfiguredCalls()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessageMatches('/Autoconfigured instanceof for type "PHPUnit[\\\\_]Framework[\\\\_]TestCase" defines method calls but these are not supported and should be removed\\./');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration(parent::class)->addMethodCall('setFoo');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
    }
    public function testProcessThrowsExceptionForArguments()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessageMatches('/Autoconfigured instanceof for type "PHPUnit[\\\\_]Framework[\\\\_]TestCase" defines arguments but these are not supported and should be removed\\./');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration(parent::class)->addArgument('bar');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
    }
    public function testMergeReset()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', self::class)->addArgument('a')->addMethodCall('setB')->setDecoratedService('foo')->addTag('t')->setInstanceofConditionals([parent::class => (new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition(''))->addTag('bar')]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        $abstract = $container->getDefinition('abstract.instanceof.bar');
        $this->assertEmpty($abstract->getArguments());
        $this->assertEmpty($abstract->getMethodCalls());
        $this->assertNull($abstract->getDecoratedService());
        $this->assertEmpty($abstract->getTags());
        $this->assertTrue($abstract->isAbstract());
    }
    public function testBindings()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo', self::class)->setBindings(['$toto' => 123]);
        $def->setInstanceofConditionals([parent::class => new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('')]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass())->process($container);
        $bindings = $container->getDefinition('foo')->getBindings();
        $this->assertSame(['$toto'], \array_keys($bindings));
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument::class, $bindings['$toto']);
        $this->assertSame(123, $bindings['$toto']->getValues()[0]);
    }
}
