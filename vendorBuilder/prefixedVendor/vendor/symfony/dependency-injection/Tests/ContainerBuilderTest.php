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

require_once __DIR__ . '/Fixtures/includes/classes.php';
require_once __DIR__ . '/Fixtures/includes/ProjectExtension.php';
use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Psr\Container\ContainerInterface as PsrContainerInterface;
use MolliePrefix\Symfony\Component\Config\Resource\ComposerResource;
use MolliePrefix\Symfony\Component\Config\Resource\DirectoryResource;
use MolliePrefix\Symfony\Component\Config\Resource\FileResource;
use MolliePrefix\Symfony\Component\Config\Resource\ResourceInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Alias;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\PassConfig;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ScalarFactory;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy;
use MolliePrefix\Symfony\Component\DependencyInjection\TypedReference;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Expression;
class ContainerBuilderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testDefaultRegisteredDefinitions()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->assertCount(1, $builder->getDefinitions());
        $this->assertTrue($builder->hasDefinition('service_container'));
        $definition = $builder->getDefinition('service_container');
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Definition::class, $definition);
        $this->assertTrue($definition->isSynthetic());
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::class, $definition->getClass());
        $this->assertTrue($builder->hasAlias(\MolliePrefix\Psr\Container\ContainerInterface::class));
        $this->assertTrue($builder->hasAlias(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::class));
    }
    public function testDefinitions()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definitions = ['foo' => new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('MolliePrefix\\Bar\\FooClass'), 'bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BarClass')];
        $builder->setDefinitions($definitions);
        $this->assertEquals($definitions, $builder->getDefinitions(), '->setDefinitions() sets the service definitions');
        $this->assertTrue($builder->hasDefinition('foo'), '->hasDefinition() returns true if a service definition exists');
        $this->assertFalse($builder->hasDefinition('foobar'), '->hasDefinition() returns false if a service definition does not exist');
        $builder->setDefinition('foobar', $foo = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooBarClass'));
        $this->assertEquals($foo, $builder->getDefinition('foobar'), '->getDefinition() returns a service definition if defined');
        $this->assertSame($builder->setDefinition('foobar', $foo = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooBarClass')), $foo, '->setDefinition() implements a fluid interface by returning the service reference');
        $builder->addDefinitions($defs = ['foobar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooBarClass')]);
        $this->assertEquals(\array_merge($definitions, $defs), $builder->getDefinitions(), '->addDefinitions() adds the service definitions');
        try {
            $builder->getDefinition('baz');
            $this->fail('->getDefinition() throws a ServiceNotFoundException if the service definition does not exist');
        } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e) {
            $this->assertEquals('You have requested a non-existent service "baz".', $e->getMessage(), '->getDefinition() throws a ServiceNotFoundException if the service definition does not exist');
        }
    }
    /**
     * @group legacy
     * @expectedDeprecation The "deprecated_foo" service is deprecated. You should stop using it, as it will soon be removed.
     */
    public function testCreateDeprecatedService()
    {
        $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass');
        $definition->setDeprecated(\true);
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setDefinition('deprecated_foo', $definition);
        $builder->get('deprecated_foo');
    }
    public function testRegister()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass');
        $this->assertTrue($builder->hasDefinition('foo'), '->register() registers a new service definition');
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Definition', $builder->getDefinition('foo'), '->register() returns the newly created Definition instance');
    }
    public function testAutowire()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->autowire('foo', 'MolliePrefix\\Bar\\FooClass');
        $this->assertTrue($builder->hasDefinition('foo'), '->autowire() registers a new service definition');
        $this->assertTrue($builder->getDefinition('foo')->isAutowired(), '->autowire() creates autowired definitions');
    }
    public function testHas()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->assertFalse($builder->has('foo'), '->has() returns false if the service does not exist');
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass');
        $this->assertTrue($builder->has('foo'), '->has() returns true if a service definition exists');
        $builder->set('bar', new \stdClass());
        $this->assertTrue($builder->has('bar'), '->has() returns true if a service exists');
    }
    public function testGetThrowsExceptionIfServiceDoesNotExist()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceNotFoundException');
        $this->expectExceptionMessage('You have requested a non-existent service "foo".');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->get('foo');
    }
    public function testGetReturnsNullIfServiceDoesNotExistAndInvalidReferenceIsUsed()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->assertNull($builder->get('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE), '->get() returns null if the service does not exist and NULL_ON_INVALID_REFERENCE is passed as a second argument');
    }
    public function testGetThrowsCircularReferenceExceptionIfServiceHasReferenceToItself()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('baz', 'stdClass')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz')]);
        $builder->get('baz');
    }
    public function testGetReturnsSameInstanceWhenServiceIsShared()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $this->assertTrue($builder->get('bar') === $builder->get('bar'), '->get() always returns the same instance if the service is shared');
    }
    public function testGetCreatesServiceBasedOnDefinition()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $this->assertIsObject($builder->get('foo'), '->get() returns the service definition associated with the id');
    }
    public function testGetReturnsRegisteredService()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->set('bar', $bar = new \stdClass());
        $this->assertSame($bar, $builder->get('bar'), '->get() returns the service associated with the id');
    }
    public function testRegisterDoesNotOverrideExistingService()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->set('bar', $bar = new \stdClass());
        $builder->register('bar', 'stdClass');
        $this->assertSame($bar, $builder->get('bar'), '->get() returns the service associated with the id even if a definition has been defined');
    }
    public function testNonSharedServicesReturnsDifferentInstances()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass')->setShared(\false);
        $this->assertNotSame($builder->get('bar'), $builder->get('bar'));
    }
    /**
     * @dataProvider provideBadId
     */
    public function testBadAliasId($id)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAlias($id, 'foo');
    }
    /**
     * @dataProvider provideBadId
     */
    public function testBadDefinitionId($id)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setDefinition($id, new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('Foo'));
    }
    public function provideBadId()
    {
        return [[''], ["\0"], ["\r"], ["\n"], ["'"], ['ab\\']];
    }
    public function testGetUnsetLoadingServiceWhenCreateServiceThrowsAnException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('You have requested a synthetic service ("foo"). The DIC does not know how to construct this service.');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'stdClass')->setSynthetic(\true);
        // we expect a RuntimeException here as foo is synthetic
        try {
            $builder->get('foo');
        } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
        }
        // we must also have the same RuntimeException here
        $builder->get('foo');
    }
    public function testGetServiceIds()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->bar = $bar = new \stdClass();
        $builder->register('bar', 'stdClass');
        $this->assertEquals(['service_container', 'foo', 'bar', 'MolliePrefix\\Psr\\Container\\ContainerInterface', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface'], $builder->getServiceIds(), '->getServiceIds() returns all defined service ids');
    }
    public function testAliases()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->setAlias('bar', 'foo');
        $this->assertTrue($builder->hasAlias('bar'), '->hasAlias() returns true if the alias exists');
        $this->assertFalse($builder->hasAlias('foobar'), '->hasAlias() returns false if the alias does not exist');
        $this->assertEquals('foo', (string) $builder->getAlias('bar'), '->getAlias() returns the aliased service');
        $this->assertTrue($builder->has('bar'), '->setAlias() defines a new service');
        $this->assertSame($builder->get('bar'), $builder->get('foo'), '->setAlias() creates a service that is an alias to another one');
        try {
            $builder->setAlias('foobar', 'foobar');
            $this->fail('->setAlias() throws an InvalidArgumentException if the alias references itself');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('An alias can not reference itself, got a circular reference on "foobar".', $e->getMessage(), '->setAlias() throws an InvalidArgumentException if the alias references itself');
        }
        try {
            $builder->getAlias('foobar');
            $this->fail('->getAlias() throws an InvalidArgumentException if the alias does not exist');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The service alias "foobar" does not exist.', $e->getMessage(), '->getAlias() throws an InvalidArgumentException if the alias does not exist');
        }
    }
    public function testGetAliases()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAlias('bar', 'foo');
        $builder->setAlias('foobar', 'foo');
        $builder->setAlias('moo', new \MolliePrefix\Symfony\Component\DependencyInjection\Alias('foo', \false));
        $aliases = $builder->getAliases();
        $this->assertEquals('foo', (string) $aliases['bar']);
        $this->assertTrue($aliases['bar']->isPublic());
        $this->assertEquals('foo', (string) $aliases['foobar']);
        $this->assertEquals('foo', (string) $aliases['moo']);
        $this->assertFalse($aliases['moo']->isPublic());
        $builder->register('bar', 'stdClass');
        $this->assertFalse($builder->hasAlias('bar'));
        $builder->set('foobar', 'stdClass');
        $builder->set('moo', 'stdClass');
        $this->assertCount(2, $builder->getAliases(), '->getAliases() does not return aliased services that have been overridden');
    }
    public function testSetAliases()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAliases(['bar' => 'foo', 'foobar' => 'foo']);
        $aliases = $builder->getAliases();
        $this->assertArrayHasKey('bar', $aliases);
        $this->assertArrayHasKey('foobar', $aliases);
    }
    public function testAddAliases()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAliases(['bar' => 'foo']);
        $builder->addAliases(['foobar' => 'foo']);
        $aliases = $builder->getAliases();
        $this->assertArrayHasKey('bar', $aliases);
        $this->assertArrayHasKey('foobar', $aliases);
    }
    public function testSetReplacesAlias()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAlias('alias', 'aliased');
        $builder->set('aliased', new \stdClass());
        $builder->set('alias', $foo = new \stdClass());
        $this->assertSame($foo, $builder->get('alias'), '->set() replaces an existing alias');
    }
    public function testAliasesKeepInvalidBehavior()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aliased = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass');
        $aliased->addMethodCall('setBar', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]);
        $builder->setDefinition('aliased', $aliased);
        $builder->setAlias('alias', 'aliased');
        $this->assertEquals(new \stdClass(), $builder->get('alias'));
    }
    public function testAddGetCompilerPass()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setResourceTracking(\false);
        $defaultPasses = $builder->getCompiler()->getPassConfig()->getPasses();
        $builder->addCompilerPass($pass1 = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface')->getMock(), \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
        $builder->addCompilerPass($pass2 = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface')->getMock(), \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $passes = $builder->getCompiler()->getPassConfig()->getPasses();
        $this->assertCount(\count($passes) - 2, $defaultPasses);
        // Pass 1 is executed later
        $this->assertTrue(\array_search($pass1, $passes, \true) > \array_search($pass2, $passes, \true));
    }
    public function testCreateService()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->setFile(__DIR__ . '/Fixtures/includes/foo.php');
        $builder->register('foo2', 'MolliePrefix\\Bar\\FooClass')->setFile(__DIR__ . '/Fixtures/includes/%file%.php');
        $builder->setParameter('file', 'foo');
        $this->assertInstanceOf('MolliePrefix\\Bar\\FooClass', $builder->get('foo1'), '->createService() requires the file defined by the service definition');
        $this->assertInstanceOf('MolliePrefix\\Bar\\FooClass', $builder->get('foo2'), '->createService() replaces parameters in the file provided by the service definition');
    }
    public function testCreateProxyWithRealServiceInstantiator()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->setFile(__DIR__ . '/Fixtures/includes/foo.php');
        $builder->getDefinition('foo1')->setLazy(\true);
        $foo1 = $builder->get('foo1');
        $this->assertSame($foo1, $builder->get('foo1'), 'The same proxy is retrieved on multiple subsequent calls');
        $this->assertSame('MolliePrefix\\Bar\\FooClass', \get_class($foo1));
    }
    public function testCreateServiceClass()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo1', '%class%');
        $builder->setParameter('class', 'stdClass');
        $this->assertInstanceOf('\\stdClass', $builder->get('foo1'), '->createService() replaces parameters in the class provided by the service definition');
    }
    public function testCreateServiceArguments()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->addArgument(['foo' => '%value%', '%value%' => 'foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), '%%unescape_it%%']);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', $builder->get('bar'), '%unescape_it%'], $builder->get('foo1')->arguments, '->createService() replaces parameters and service references in the arguments provided by the service definition');
    }
    public function testCreateServiceFactory()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass')->setFactory('Bar\\FooClass::getInstance');
        $builder->register('qux', 'MolliePrefix\\Bar\\FooClass')->setFactory(['MolliePrefix\\Bar\\FooClass', 'getInstance']);
        $builder->register('bar', 'MolliePrefix\\Bar\\FooClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('MolliePrefix\\Bar\\FooClass'), 'getInstance']);
        $builder->register('baz', 'MolliePrefix\\Bar\\FooClass')->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), 'getInstance']);
        $this->assertTrue($builder->get('foo')->called, '->createService() calls the factory method to create the service instance');
        $this->assertTrue($builder->get('qux')->called, '->createService() calls the factory method to create the service instance');
        $this->assertTrue($builder->get('bar')->called, '->createService() uses anonymous service as factory');
        $this->assertTrue($builder->get('baz')->called, '->createService() uses another service as factory');
    }
    public function testCreateServiceMethodCalls()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->addMethodCall('setBar', [['%value%', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]]);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['bar', $builder->get('bar')], $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }
    public function testCreateServiceMethodCallsWithEscapedParam()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->addMethodCall('setBar', [['%%unescape_it%%']]);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['%unescape_it%'], $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }
    public function testCreateServiceProperties()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->setProperty('bar', ['%value%', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), '%%unescape_it%%']);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['bar', $builder->get('bar'), '%unescape_it%'], $builder->get('foo1')->bar, '->createService() replaces the values in the properties');
    }
    public function testCreateServiceConfigurator()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo1', 'MolliePrefix\\Bar\\FooClass')->setConfigurator('sc_configure');
        $builder->register('foo2', 'MolliePrefix\\Bar\\FooClass')->setConfigurator(['%class%', 'configureStatic']);
        $builder->setParameter('class', 'BazClass');
        $builder->register('baz', 'BazClass');
        $builder->register('foo3', 'MolliePrefix\\Bar\\FooClass')->setConfigurator([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz'), 'configure']);
        $builder->register('foo4', 'MolliePrefix\\Bar\\FooClass')->setConfigurator([$builder->getDefinition('baz'), 'configure']);
        $builder->register('foo5', 'MolliePrefix\\Bar\\FooClass')->setConfigurator('foo');
        $this->assertTrue($builder->get('foo1')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo2')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo3')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo4')->configured, '->createService() calls the configurator');
        try {
            $builder->get('foo5');
            $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The configure callable for class "Bar\\FooClass" is not a callable.', $e->getMessage(), '->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        }
    }
    public function testCreateServiceWithIteratorArgument()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('lazy_context', 'LazyContext')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument(['k1' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('invalid', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]), new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\IteratorArgument([])]);
        $lazyContext = $builder->get('lazy_context');
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator::class, $lazyContext->lazyValues);
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\Argument\RewindableGenerator::class, $lazyContext->lazyEmptyValues);
        $this->assertCount(1, $lazyContext->lazyValues);
        $this->assertCount(0, $lazyContext->lazyEmptyValues);
        $i = 0;
        foreach ($lazyContext->lazyValues as $k => $v) {
            ++$i;
            $this->assertEquals('k1', $k);
            $this->assertInstanceOf('\\stdClass', $v);
        }
        // The second argument should have been ignored.
        $this->assertEquals(1, $i);
        $i = 0;
        foreach ($lazyContext->lazyEmptyValues as $k => $v) {
            ++$i;
        }
        $this->assertEquals(0, $i);
    }
    public function testCreateSyntheticService()
    {
        $this->expectException('RuntimeException');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass')->setSynthetic(\true);
        $builder->get('foo');
    }
    public function testCreateServiceWithExpression()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setParameter('bar', 'bar');
        $builder->register('bar', 'BarClass');
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass')->addArgument(['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Expression('service("bar").foo ~ parameter("bar")')]);
        $this->assertEquals('foobar', $builder->get('foo')->arguments['foo']);
    }
    public function testResolveServices()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')), '->resolveServices() resolves service references to service instances');
        $this->assertEquals(['foo' => ['foo', $builder->get('foo')]], $builder->resolveServices(['foo' => ['foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]]), '->resolveServices() resolves service references to service instances in nested arrays');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Expression('service("foo")')), '->resolveServices() resolves expressions');
    }
    public function testResolveServicesWithDecoratedDefinition()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Constructing service "foo" from a parent definition is not supported at build time.');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setDefinition('grandpa', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass'));
        $builder->setDefinition('parent', new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('grandpa'));
        $builder->setDefinition('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\ChildDefinition('parent'));
        $builder->get('foo');
    }
    public function testResolveServicesWithCustomDefinitionClass()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setDefinition('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition('stdClass'));
        $this->assertInstanceOf('stdClass', $builder->get('foo'));
    }
    public function testMerge()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(\false);
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['foo' => 'bar']));
        $container->merge($config);
        $this->assertEquals(['bar' => 'foo', 'foo' => 'bar'], $container->getParameterBag()->all(), '->merge() merges current parameters with the loaded ones');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(\false);
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['foo' => '%bar%']));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(['bar' => 'foo', 'foo' => 'foo'], $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(\false);
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['foo' => '%bar%', 'baz' => '%foo%']));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(['bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'], $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->register('foo', 'MolliePrefix\\Bar\\FooClass');
        $container->register('bar', 'BarClass');
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $config->setDefinition('baz', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BazClass'));
        $config->setAlias('alias_for_foo', 'foo');
        $container->merge($config);
        $this->assertEquals(['service_container', 'foo', 'bar', 'baz'], \array_keys($container->getDefinitions()), '->merge() merges definitions already defined ones');
        $aliases = $container->getAliases();
        $this->assertArrayHasKey('alias_for_foo', $aliases);
        $this->assertEquals('foo', (string) $aliases['alias_for_foo']);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->register('foo', 'MolliePrefix\\Bar\\FooClass');
        $config->setDefinition('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BazClass'));
        $container->merge($config);
        $this->assertEquals('BazClass', $container->getDefinition('foo')->getClass(), '->merge() overrides already defined services');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bag = new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag();
        $bag->get('env(Foo)');
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder($bag);
        $this->assertSame(['%env(Bar)%'], $config->resolveEnvPlaceholders([$bag->get('env(Bar)')]));
        $container->merge($config);
        $this->assertEquals(['Foo' => 0, 'Bar' => 1], $container->getEnvCounters());
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $childDefA = $container->registerForAutoconfiguration('AInterface');
        $childDefB = $config->registerForAutoconfiguration('BInterface');
        $container->merge($config);
        $this->assertSame(['AInterface' => $childDefA, 'BInterface' => $childDefB], $container->getAutoconfiguredInstanceof());
    }
    public function testMergeThrowsExceptionForDuplicateAutomaticInstanceofDefinitions()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('"AInterface" has already been autoconfigured and merge() does not support merging autoconfiguration for the same class/interface.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $config = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->registerForAutoconfiguration('AInterface');
        $config->registerForAutoconfiguration('AInterface');
        $container->merge($config);
    }
    public function testResolveEnvValues()
    {
        $_ENV['DUMMY_ENV_VAR'] = 'du%%y';
        $_SERVER['DUMMY_SERVER_VAR'] = 'ABC';
        $_SERVER['HTTP_DUMMY_VAR'] = 'DEF';
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('bar', '%% %env(DUMMY_ENV_VAR)% %env(DUMMY_SERVER_VAR)% %env(HTTP_DUMMY_VAR)%');
        $container->setParameter('env(HTTP_DUMMY_VAR)', '123');
        $this->assertSame('%% du%%%%y ABC 123', $container->resolveEnvPlaceholders('%bar%', \true));
        unset($_ENV['DUMMY_ENV_VAR'], $_SERVER['DUMMY_SERVER_VAR'], $_SERVER['HTTP_DUMMY_VAR']);
    }
    public function testResolveEnvValuesWithArray()
    {
        $_ENV['ANOTHER_DUMMY_ENV_VAR'] = 'dummy';
        $dummyArray = ['1' => 'one', '2' => 'two'];
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('dummy', '%env(ANOTHER_DUMMY_ENV_VAR)%');
        $container->setParameter('dummy2', $dummyArray);
        $container->resolveEnvPlaceholders('%dummy%', \true);
        $container->resolveEnvPlaceholders('%dummy2%', \true);
        $this->assertIsArray($container->resolveEnvPlaceholders('%dummy2%', \true));
        foreach ($dummyArray as $key => $value) {
            $this->assertArrayHasKey($key, $container->resolveEnvPlaceholders('%dummy2%', \true));
        }
        unset($_ENV['ANOTHER_DUMMY_ENV_VAR']);
    }
    public function testCompileWithResolveEnv()
    {
        \putenv('DUMMY_ENV_VAR=du%%y');
        $_SERVER['DUMMY_SERVER_VAR'] = 'ABC';
        $_SERVER['HTTP_DUMMY_VAR'] = 'DEF';
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(FOO)', 'Foo');
        $container->setParameter('env(DUMMY_ENV_VAR)', 'GHI');
        $container->setParameter('bar', '%% %env(DUMMY_ENV_VAR)% %env(DUMMY_SERVER_VAR)% %env(HTTP_DUMMY_VAR)%');
        $container->setParameter('foo', '%env(FOO)%');
        $container->setParameter('baz', '%foo%');
        $container->setParameter('env(HTTP_DUMMY_VAR)', '123');
        $container->register('teatime', 'stdClass')->setProperty('foo', '%env(DUMMY_ENV_VAR)%')->setPublic(\true);
        $container->compile(\true);
        $this->assertSame('% du%%y ABC 123', $container->getParameter('bar'));
        $this->assertSame('Foo', $container->getParameter('baz'));
        $this->assertSame('du%%y', $container->get('teatime')->foo);
        unset($_SERVER['DUMMY_SERVER_VAR'], $_SERVER['HTTP_DUMMY_VAR']);
        \putenv('DUMMY_ENV_VAR');
    }
    public function testCompileWithArrayResolveEnv()
    {
        \putenv('ARRAY={"foo":"bar"}');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', '%env(json:ARRAY)%');
        $container->compile(\true);
        $this->assertSame(['foo' => 'bar'], $container->getParameter('foo'));
        \putenv('ARRAY');
    }
    public function testCompileWithArrayAndAnotherResolveEnv()
    {
        \putenv('DUMMY_ENV_VAR=abc');
        \putenv('ARRAY={"foo":"bar"}');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', '%env(json:ARRAY)%');
        $container->setParameter('bar', '%env(DUMMY_ENV_VAR)%');
        $container->compile(\true);
        $this->assertSame(['foo' => 'bar'], $container->getParameter('foo'));
        $this->assertSame('abc', $container->getParameter('bar'));
        \putenv('DUMMY_ENV_VAR');
        \putenv('ARRAY');
    }
    public function testCompileWithArrayInStringResolveEnv()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('A string value must be composed of strings and/or numbers, but found parameter "env(json:ARRAY)" of type "array" inside string value "ABC %env(json:ARRAY)%".');
        \putenv('ARRAY={"foo":"bar"}');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', 'ABC %env(json:ARRAY)%');
        $container->compile(\true);
        \putenv('ARRAY');
    }
    public function testCompileWithResolveMissingEnv()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\EnvNotFoundException');
        $this->expectExceptionMessage('Environment variable not found: "FOO".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', '%env(FOO)%');
        $container->compile(\true);
    }
    public function testDynamicEnv()
    {
        \putenv('DUMMY_FOO=some%foo%');
        \putenv('DUMMY_BAR=%bar%');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', 'Foo%env(resolve:DUMMY_BAR)%');
        $container->setParameter('bar', 'Bar');
        $container->setParameter('baz', '%env(resolve:DUMMY_FOO)%');
        $container->compile(\true);
        \putenv('DUMMY_FOO');
        \putenv('DUMMY_BAR');
        $this->assertSame('someFooBar', $container->getParameter('baz'));
    }
    public function testCastEnv()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(FAKE)', '123');
        $container->register('foo', 'stdClass')->setPublic(\true)->setProperties(['fake' => '%env(int:FAKE)%']);
        $container->compile(\true);
        $this->assertSame(123, $container->get('foo')->fake);
    }
    public function testEnvAreNullable()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('env(FAKE)', null);
        $container->register('foo', 'stdClass')->setPublic(\true)->setProperties(['fake' => '%env(int:FAKE)%']);
        $container->compile(\true);
        $this->assertNull($container->get('foo')->fake);
    }
    public function testEnvInId()
    {
        $container = (include __DIR__ . '/Fixtures/containers/container_env_in_id.php');
        $container->compile(\true);
        $expected = ['service_container', 'foo', 'bar', 'bar_%env(BAR)%'];
        $this->assertSame($expected, \array_keys($container->getDefinitions()));
        $expected = [\MolliePrefix\Psr\Container\ContainerInterface::class => \true, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::class => \true, 'baz_%env(BAR)%' => \true, 'bar_%env(BAR)%' => \true];
        $this->assertSame($expected, $container->getRemovedIds());
        $this->assertSame(['baz_bar'], \array_keys($container->getDefinition('foo')->getArgument(1)));
    }
    public function testCircularDynamicEnv()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ParameterCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for parameter "env(resolve:DUMMY_ENV_VAR)" ("env(resolve:DUMMY_ENV_VAR)" > "env(resolve:DUMMY_ENV_VAR)").');
        \putenv('DUMMY_ENV_VAR=some%foo%');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('foo', '%bar%');
        $container->setParameter('bar', '%env(resolve:DUMMY_ENV_VAR)%');
        try {
            $container->compile(\true);
        } finally {
            \putenv('DUMMY_ENV_VAR');
        }
    }
    public function testMergeLogicException()
    {
        $this->expectException('LogicException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->compile();
        $container->merge(new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder());
    }
    public function testfindTaggedServiceIds()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass')->addTag('foo', ['foo' => 'foo'])->addTag('bar', ['bar' => 'bar'])->addTag('foo', ['foofoo' => 'foofoo']);
        $this->assertEquals(['foo' => [['foo' => 'foo'], ['foofoo' => 'foofoo']]], $builder->findTaggedServiceIds('foo'), '->findTaggedServiceIds() returns an array of service ids and its tag attributes');
        $this->assertEquals([], $builder->findTaggedServiceIds('foobar'), '->findTaggedServiceIds() returns an empty array if there is annotated services');
    }
    public function testFindUnusedTags()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('foo', 'MolliePrefix\\Bar\\FooClass')->addTag('kernel.event_listener', ['foo' => 'foo'])->addTag('kenrel.event_listener', ['bar' => 'bar']);
        $builder->findTaggedServiceIds('kernel.event_listener');
        $this->assertEquals(['kenrel.event_listener'], $builder->findUnusedTags(), '->findUnusedTags() returns an array with unused tags');
    }
    public function testFindDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setDefinition('foo', $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('MolliePrefix\\Bar\\FooClass'));
        $container->setAlias('bar', 'foo');
        $container->setAlias('foobar', 'bar');
        $this->assertEquals($definition, $container->findDefinition('foobar'), '->findDefinition() returns a Definition');
    }
    public function testAddObjectResource()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->addObjectResource(new \MolliePrefix\BarClass());
        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');
        $container->setResourceTracking(\true);
        $container->addObjectResource(new \MolliePrefix\BarClass());
        $resources = $container->getResources();
        $this->assertCount(2, $resources, '2 resources were registered');
        /* @var $resource \Symfony\Component\Config\Resource\FileResource */
        $resource = \end($resources);
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Resource\\FileResource', $resource);
        $this->assertSame(\realpath(__DIR__ . '/Fixtures/includes/classes.php'), \realpath($resource->getResource()));
    }
    /**
     * @group legacy
     */
    public function testAddClassResource()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->addClassResource(new \ReflectionClass('BarClass'));
        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');
        $container->setResourceTracking(\true);
        $container->addClassResource(new \ReflectionClass('BarClass'));
        $resources = $container->getResources();
        $this->assertCount(2, $resources, '2 resources were registered');
        /* @var $resource \Symfony\Component\Config\Resource\FileResource */
        $resource = \end($resources);
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Resource\\FileResource', $resource);
        $this->assertSame(\realpath(__DIR__ . '/Fixtures/includes/classes.php'), \realpath($resource->getResource()));
    }
    public function testGetReflectionClass()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $r1 = $container->getReflectionClass('BarClass');
        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');
        $container->setResourceTracking(\true);
        $r2 = $container->getReflectionClass('BarClass');
        $r3 = $container->getReflectionClass('BarClass');
        $this->assertNull($container->getReflectionClass('BarMissingClass'));
        $this->assertEquals($r1, $r2);
        $this->assertSame($r2, $r3);
        $resources = $container->getResources();
        $this->assertCount(3, $resources, '3 resources were registered');
        $this->assertSame('reflection.BarClass', (string) $resources[1]);
        $this->assertSame('BarMissingClass', (string) \end($resources));
    }
    public function testGetReflectionClassOnInternalTypes()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->assertNull($container->getReflectionClass('int'));
        $this->assertNull($container->getReflectionClass('float'));
        $this->assertNull($container->getReflectionClass('string'));
        $this->assertNull($container->getReflectionClass('bool'));
        $this->assertNull($container->getReflectionClass('resource'));
        $this->assertNull($container->getReflectionClass('object'));
        $this->assertNull($container->getReflectionClass('array'));
        $this->assertNull($container->getReflectionClass('null'));
        $this->assertNull($container->getReflectionClass('callable'));
        $this->assertNull($container->getReflectionClass('iterable'));
        $this->assertNull($container->getReflectionClass('mixed'));
    }
    public function testCompilesClassDefinitionsOfLazyServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');
        $container->register('foo', 'BarClass')->setPublic(\true);
        $container->getDefinition('foo')->setLazy(\true);
        $container->compile();
        $matchingResources = \array_filter($container->getResources(), function (\MolliePrefix\Symfony\Component\Config\Resource\ResourceInterface $resource) {
            return 'reflection.BarClass' === (string) $resource;
        });
        $this->assertNotEmpty($matchingResources);
    }
    public function testResources()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->addResource($a = new \MolliePrefix\Symfony\Component\Config\Resource\FileResource(__DIR__ . '/Fixtures/xml/services1.xml'));
        $container->addResource($b = new \MolliePrefix\Symfony\Component\Config\Resource\FileResource(__DIR__ . '/Fixtures/xml/services2.xml'));
        $resources = [];
        foreach ($container->getResources() as $resource) {
            if (\false === \strpos($resource, '.php')) {
                $resources[] = $resource;
            }
        }
        $this->assertEquals([$a, $b], $resources, '->getResources() returns an array of resources read for the current configuration');
        $this->assertSame($container, $container->setResources([]));
        $this->assertEquals([], $container->getResources());
    }
    public function testFileExists()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $A = new \MolliePrefix\Symfony\Component\Config\Resource\ComposerResource();
        $a = new \MolliePrefix\Symfony\Component\Config\Resource\FileResource(__DIR__ . '/Fixtures/xml/services1.xml');
        $b = new \MolliePrefix\Symfony\Component\Config\Resource\FileResource(__DIR__ . '/Fixtures/xml/services2.xml');
        $c = new \MolliePrefix\Symfony\Component\Config\Resource\DirectoryResource($dir = \dirname($b));
        $this->assertTrue($container->fileExists((string) $a) && $container->fileExists((string) $b) && $container->fileExists($dir));
        $resources = [];
        foreach ($container->getResources() as $resource) {
            if (\false === \strpos($resource, '.php')) {
                $resources[] = $resource;
            }
        }
        $this->assertEquals([$A, $a, $b, $c], $resources, '->getResources() returns an array of resources read for the current configuration');
    }
    public function testExtension()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->registerExtension($extension = new \MolliePrefix\ProjectExtension());
        $this->assertSame($container->getExtension('project'), $extension, '->registerExtension() registers an extension');
        $this->expectException('LogicException');
        $container->getExtension('no_registered');
    }
    public function testRegisteredButNotLoadedExtension()
    {
        $extension = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface')->getMock();
        $extension->expects($this->once())->method('getAlias')->willReturn('project');
        $extension->expects($this->never())->method('load');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->registerExtension($extension);
        $container->compile();
    }
    public function testRegisteredAndLoadedExtension()
    {
        $extension = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface')->getMock();
        $extension->expects($this->exactly(2))->method('getAlias')->willReturn('project');
        $extension->expects($this->once())->method('load')->with([['foo' => 'bar']]);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->registerExtension($extension);
        $container->loadFromExtension('project', ['foo' => 'bar']);
        $container->compile();
    }
    public function testPrivateServiceUser()
    {
        $fooDefinition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BarClass');
        $fooUserDefinition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BarUserClass', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $fooDefinition->setPublic(\false);
        $container->addDefinitions(['bar' => $fooDefinition, 'bar_user' => $fooUserDefinition->setPublic(\true)]);
        $container->compile();
        $this->assertInstanceOf('BarClass', $container->get('bar_user')->bar);
    }
    public function testThrowsExceptionWhenSetServiceOnACompiledContainer()
    {
        $this->expectException('BadMethodCallException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->register('a', 'stdClass')->setPublic(\true);
        $container->compile();
        $container->set('a', new \stdClass());
    }
    public function testNoExceptionWhenAddServiceOnACompiledContainer()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->compile();
        $container->set('a', $foo = new \stdClass());
        $this->assertSame($foo, $container->get('a'));
    }
    public function testNoExceptionWhenSetSyntheticServiceOnACompiledContainer()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass');
        $def->setSynthetic(\true)->setPublic(\true);
        $container->setDefinition('a', $def);
        $container->compile();
        $container->set('a', $a = new \stdClass());
        $this->assertEquals($a, $container->get('a'));
    }
    public function testThrowsExceptionWhenSetDefinitionOnACompiledContainer()
    {
        $this->expectException('BadMethodCallException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setResourceTracking(\false);
        $container->compile();
        $container->setDefinition('a', new \MolliePrefix\Symfony\Component\DependencyInjection\Definition());
    }
    public function testExtensionConfig()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $configs = $container->getExtensionConfig('foo');
        $this->assertEmpty($configs);
        $first = ['foo' => 'bar'];
        $container->prependExtensionConfig('foo', $first);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals([$first], $configs);
        $second = ['ding' => 'dong'];
        $container->prependExtensionConfig('foo', $second);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals([$second, $first], $configs);
    }
    public function testAbstractAlias()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $abstract = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('AbstractClass');
        $abstract->setAbstract(\true)->setPublic(\true);
        $container->setDefinition('abstract_service', $abstract);
        $container->setAlias('abstract_alias', 'abstract_service')->setPublic(\true);
        $container->compile();
        $this->assertSame('abstract_service', (string) $container->getAlias('abstract_alias'));
    }
    public function testLazyLoadedService()
    {
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder());
        $loader->load(function (\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
            $container->set('a', new \MolliePrefix\BazClass());
            $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BazClass');
            $definition->setLazy(\true);
            $definition->setPublic(\true);
            $container->setDefinition('a', $definition);
        });
        $container->setResourceTracking(\true);
        $container->compile();
        $r = new \ReflectionProperty($container, 'resources');
        $r->setAccessible(\true);
        $resources = $r->getValue($container);
        $classInList = \false;
        foreach ($resources as $resource) {
            if ('reflection.BazClass' === (string) $resource) {
                $classInList = \true;
                break;
            }
        }
        $this->assertTrue($classInList);
    }
    public function testInlinedDefinitions()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BarClass');
        $container->register('bar_user', 'BarUserClass')->addArgument($definition)->setProperty('foo', $definition);
        $container->register('bar', 'BarClass')->setProperty('foo', $definition)->addMethodCall('setBaz', [$definition]);
        $barUser = $container->get('bar_user');
        $bar = $container->get('bar');
        $this->assertSame($barUser->foo, $barUser->bar);
        $this->assertSame($bar->foo, $bar->getBaz());
        $this->assertNotSame($bar->foo, $barUser->foo);
    }
    public function testThrowsCircularExceptionForCircularAliases()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for service "app.test_class", path: "app.test_class -> App\\TestClass -> app.test_class".');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->setAliases(['foo' => new \MolliePrefix\Symfony\Component\DependencyInjection\Alias('app.test_class'), 'app.test_class' => new \MolliePrefix\Symfony\Component\DependencyInjection\Alias('MolliePrefix\\App\\TestClass'), 'MolliePrefix\\App\\TestClass' => new \MolliePrefix\Symfony\Component\DependencyInjection\Alias('app.test_class')]);
        $builder->findDefinition('foo');
    }
    public function testInitializePropertiesBeforeMethodCalls()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass');
        $container->register('bar', 'MethodCallClass')->setPublic(\true)->setProperty('simple', 'bar')->setProperty('complex', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'))->addMethodCall('callMe');
        $container->compile();
        $this->assertTrue($container->get('bar')->callPassed(), '->compile() initializes properties before method calls');
    }
    public function testAutowiring()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\A::class)->setPublic(\true);
        $bDefinition = $container->register('b', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\B::class);
        $bDefinition->setAutowired(\true);
        $bDefinition->setPublic(\true);
        $container->compile();
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\A::class, (string) $container->getDefinition('b')->getArgument(0));
    }
    public function testClassFromId()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $unknown = $container->register('MolliePrefix\\Acme\\UnknownClass');
        $autoloadClass = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class);
        $container->compile();
        $this->assertSame('MolliePrefix\\Acme\\UnknownClass', $unknown->getClass());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class, $autoloadClass->getClass());
    }
    public function testNoClassFromGlobalNamespaceClassId()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The definition for "DateTime" has no class attribute, and appears to reference a class or interface in the global namespace.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\DateTime::class);
        $container->compile();
    }
    public function testNoClassFromGlobalNamespaceClassIdWithLeadingSlash()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The definition for "\\DateTime" has no class attribute, and appears to reference a class or interface in the global namespace.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('\\' . \DateTime::class);
        $container->compile();
    }
    public function testNoClassFromNamespaceClassIdWithLeadingSlash()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The definition for "\\Symfony\\Component\\DependencyInjection\\Tests\\FooClass" has no class attribute, and appears to reference a class or interface. Please specify the class attribute explicitly or remove the leading backslash by renaming the service to "Symfony\\Component\\DependencyInjection\\Tests\\FooClass" to get rid of this error.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('\\' . \MolliePrefix\Symfony\Component\DependencyInjection\Tests\FooClass::class);
        $container->compile();
    }
    public function testNoClassFromNonClassId()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The definition for "123_abc" has no class.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('123_abc');
        $container->compile();
    }
    public function testNoClassFromNsSeparatorId()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The definition for "\\foo" has no class.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('\\foo');
        $container->compile();
    }
    public function testServiceLocator()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo_service', \MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class)->setPublic(\true)->addArgument(['bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar_service')), 'baz' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference('baz_service', 'stdClass'))]);
        $container->register('bar_service', 'stdClass')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz_service')])->setPublic(\true);
        $container->register('baz_service', 'stdClass')->setPublic(\false);
        $container->compile();
        $this->assertInstanceOf(\MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class, $foo = $container->get('foo_service'));
        $this->assertSame($container->get('bar_service'), $foo->get('bar'));
    }
    public function testUninitializedReference()
    {
        $container = (include __DIR__ . '/Fixtures/containers/container_uninitialized_ref.php');
        $container->compile();
        $bar = $container->get('bar');
        $this->assertNull($bar->foo1);
        $this->assertNull($bar->foo2);
        $this->assertNull($bar->foo3);
        $this->assertNull($bar->closures[0]());
        $this->assertNull($bar->closures[1]());
        $this->assertNull($bar->closures[2]());
        $this->assertSame([], \iterator_to_array($bar->iter));
        $container = (include __DIR__ . '/Fixtures/containers/container_uninitialized_ref.php');
        $container->compile();
        $container->get('foo1');
        $container->get('baz');
        $bar = $container->get('bar');
        $this->assertEquals(new \stdClass(), $bar->foo1);
        $this->assertNull($bar->foo2);
        $this->assertEquals(new \stdClass(), $bar->foo3);
        $this->assertEquals(new \stdClass(), $bar->closures[0]());
        $this->assertNull($bar->closures[1]());
        $this->assertEquals(new \stdClass(), $bar->closures[2]());
        $this->assertEquals(['foo1' => new \stdClass(), 'foo3' => new \stdClass()], \iterator_to_array($bar->iter));
    }
    /**
     * @dataProvider provideAlmostCircular
     */
    public function testAlmostCircular($visibility)
    {
        $container = (include __DIR__ . '/Fixtures/containers/container_almost_circular.php');
        $foo = $container->get('foo');
        $this->assertSame($foo, $foo->bar->foobar->foo);
        $foo2 = $container->get('foo2');
        $this->assertSame($foo2, $foo2->bar->foobar->foo);
        $this->assertSame([], (array) $container->get('foobar4'));
        $foo5 = $container->get('foo5');
        $this->assertSame($foo5, $foo5->bar->foo);
        $manager = $container->get('manager');
        $this->assertEquals(new \stdClass(), $manager);
        $manager = $container->get('manager2');
        $this->assertEquals(new \stdClass(), $manager);
        $foo6 = $container->get('foo6');
        $this->assertEquals((object) ['bar6' => (object) []], $foo6);
        $this->assertInstanceOf(\stdClass::class, $container->get('root'));
        $manager3 = $container->get('manager3');
        $listener3 = $container->get('listener3');
        $this->assertSame($manager3, $listener3->manager, 'Both should identically be the manager3 service');
        $listener4 = $container->get('listener4');
        $this->assertInstanceOf('stdClass', $listener4);
    }
    public function provideAlmostCircular()
    {
        (yield ['public']);
        (yield ['private']);
    }
    public function testRegisterForAutoconfiguration()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $childDefA = $container->registerForAutoconfiguration('AInterface');
        $childDefB = $container->registerForAutoconfiguration('BInterface');
        $this->assertSame(['AInterface' => $childDefA, 'BInterface' => $childDefB], $container->getAutoconfiguredInstanceof());
        // when called multiple times, the same instance is returned
        $this->assertSame($childDefA, $container->registerForAutoconfiguration('AInterface'));
    }
    /**
     * This test checks the trigger of a deprecation note and should not be removed in major releases.
     *
     * @group legacy
     * @expectedDeprecation The "foo" service is deprecated. You should stop using it, as it will soon be removed.
     */
    public function testPrivateServiceTriggersDeprecation()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(\false)->setDeprecated(\true);
        $container->register('bar', 'stdClass')->setPublic(\true)->setProperty('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'));
        $container->compile();
        $container->get('bar');
    }
    /**
     * @group legacy
     * @expectedDeprecation Parameter names will be made case sensitive in Symfony 4.0. Using "FOO" instead of "foo" is deprecated since Symfony 3.4.
     */
    public function testParameterWithMixedCase()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder(new \MolliePrefix\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['foo' => 'bar']));
        $container->register('foo', 'stdClass')->setPublic(\true)->setProperty('foo', '%FOO%');
        $container->compile();
        $this->assertSame('bar', $container->get('foo')->foo);
    }
    public function testArgumentsHaveHigherPriorityThanBindings()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('class.via.bindings', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class)->setArguments(['via-bindings']);
        $container->register('class.via.argument', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class)->setArguments(['via-argument']);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class)->setPublic(\true)->setBindings([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('class.via.bindings'), '$token' => '1234'])->setArguments(['$class1' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('class.via.argument')]);
        $this->assertSame(['service_container', 'class.via.bindings', 'class.via.argument', 'foo', 'MolliePrefix\\Psr\\Container\\ContainerInterface', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\ContainerInterface'], $container->getServiceIds());
        $container->compile();
        $this->assertSame('via-argument', $container->get('foo')->class1->identifier);
        $this->assertSame('via-bindings', $container->get('foo')->class2->identifier);
    }
    public function testUninitializedSyntheticReference()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(\true)->setSynthetic(\true);
        $container->register('bar', 'stdClass')->setPublic(\true)->setShared(\false)->setProperty('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE));
        $container->compile();
        $this->assertEquals((object) ['foo' => null], $container->get('bar'));
        $container->set('foo', (object) [123]);
        $this->assertEquals((object) ['foo' => (object) [123]], $container->get('bar'));
    }
    public function testDecoratedSelfReferenceInvolvingPrivateServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(\false)->setProperty('bar', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'));
        $container->register('baz', 'stdClass')->setPublic(\false)->setProperty('inner', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz.inner'))->setDecoratedService('foo');
        $container->compile();
        $this->assertSame(['service_container'], \array_keys($container->getDefinitions()));
    }
    public function testScalarService()
    {
        $c = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $c->register('foo', 'string')->setPublic(\true)->setFactory([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ScalarFactory::class, 'getSomeValue']);
        $c->compile();
        $this->assertTrue($c->has('foo'));
        $this->assertSame('some value', $c->get('foo'));
    }
}
class FooClass
{
}
class A
{
}
class B
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\A $a)
    {
    }
}
