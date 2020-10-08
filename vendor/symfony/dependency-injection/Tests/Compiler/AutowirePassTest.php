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
use MolliePrefix\Symfony\Component\Config\FileLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\FooVariadic;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\MultipleArgumentsOptionalScalarNotReallyOptional;
use MolliePrefix\Symfony\Component\DependencyInjection\TypedReference;
require_once __DIR__ . '/../Fixtures/includes/autowiring_classes.php';
/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AutowirePassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $barDefinition = $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class);
        $barDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(1, $container->getDefinition('bar')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, (string) $container->getDefinition('bar')->getArgument(0));
    }
    /**
     * @requires PHP 5.6
     */
    public function testProcessVariadic()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $definition = $container->register('fooVariadic', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\FooVariadic::class);
        $definition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(1, $container->getDefinition('fooVariadic')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, (string) $container->getDefinition('fooVariadic')->getArgument(0));
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. You should alias the "Symfony\Component\DependencyInjection\Tests\Compiler\B" service to "Symfony\Component\DependencyInjection\Tests\Compiler\A" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "c": argument "$a" of method "Symfony\Component\DependencyInjection\Tests\Compiler\C::__construct()" references class "Symfony\Component\DependencyInjection\Tests\Compiler\A" but no such service exists. You should maybe alias this class to the existing "Symfony\Component\DependencyInjection\Tests\Compiler\B" service.
     */
    public function testProcessAutowireParent()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $cDefinition = $container->register('c', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\C::class);
        $cDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(1, $container->getDefinition('c')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class, (string) $container->getDefinition('c')->getArgument(0));
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. Try changing the type-hint for argument "$a" of method "Symfony\Component\DependencyInjection\Tests\Compiler\C::__construct()" to "Symfony\Component\DependencyInjection\Tests\Compiler\AInterface" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "c": argument "$a" of method "Symfony\Component\DependencyInjection\Tests\Compiler\C::__construct()" references class "Symfony\Component\DependencyInjection\Tests\Compiler\A" but no such service exists. You should maybe alias this class to the existing "Symfony\Component\DependencyInjection\Tests\Compiler\B" service.
     */
    public function testProcessLegacyAutowireWithAvailableInterface()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\AInterface::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $cDefinition = $container->register('c', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\C::class);
        $cDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(1, $container->getDefinition('c')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class, (string) $container->getDefinition('c')->getArgument(0));
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. You should alias the "Symfony\Component\DependencyInjection\Tests\Compiler\F" service to "Symfony\Component\DependencyInjection\Tests\Compiler\DInterface" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "g": argument "$d" of method "Symfony\Component\DependencyInjection\Tests\Compiler\G::__construct()" references interface "Symfony\Component\DependencyInjection\Tests\Compiler\DInterface" but no such service exists. You should maybe alias this interface to the existing "Symfony\Component\DependencyInjection\Tests\Compiler\F" service.
     */
    public function testProcessAutowireInterface()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class);
        $gDefinition = $container->register('g', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\G::class);
        $gDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(3, $container->getDefinition('g')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class, (string) $container->getDefinition('g')->getArgument(0));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class, (string) $container->getDefinition('g')->getArgument(1));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class, (string) $container->getDefinition('g')->getArgument(2));
    }
    public function testCompleteExistingDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('b', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class);
        $hDefinition = $container->register('h', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\H::class)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('b'));
        $hDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(2, $container->getDefinition('h')->getArguments());
        $this->assertEquals('b', (string) $container->getDefinition('h')->getArgument(0));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface::class, (string) $container->getDefinition('h')->getArgument(1));
    }
    public function testCompleteExistingDefinitionWithNotDefinedArguments()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\F::class);
        $hDefinition = $container->register('h', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\H::class)->addArgument('')->addArgument('');
        $hDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(2, $container->getDefinition('h')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class, (string) $container->getDefinition('h')->getArgument(0));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface::class, (string) $container->getDefinition('h')->getArgument(1));
    }
    /**
     * @group legacy
     */
    public function testExceptionsAreStored()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('c1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register('c2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $container->register('c3', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CannotBeAutowired::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass(\false);
        $pass->process($container);
        $this->assertCount(1, $pass->getAutowiringExceptions());
    }
    public function testPrivateConstructorThrowsAutowireException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Invalid service "private_service": constructor of class "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\PrivateConstructor" must be public.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->autowire('private_service', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\PrivateConstructor::class);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass(\true);
        $pass->process($container);
    }
    public function testTypeCollision()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$collision" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CannotBeAutowired::__construct()" references interface "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CollisionInterface" but no such service exists. You should maybe alias this interface to one of these existing services: "c1", "c2", "c3".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('c1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register('c2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $container->register('c3', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CannotBeAutowired::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testTypeNotGuessable()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$k" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotGuessableArgument::__construct()" references class "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Foo" but no such service exists. You should maybe alias this class to one of these existing services: "a1", "a2".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register('a2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotGuessableArgument::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testTypeNotGuessableWithSubclass()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$k" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotGuessableArgumentForSubclass::__construct()" references class "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\A" but no such service exists. You should maybe alias this class to one of these existing services: "a1", "a2".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $container->register('a2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotGuessableArgumentForSubclass::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testTypeNotGuessableNoServicesFound()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$collision" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CannotBeAutowired::__construct()" references interface "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CollisionInterface" but no such service exists.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CannotBeAutowired::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    /**
     * @requires PHP 8
     */
    public function testTypeNotGuessableUnionType()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$collision" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\UnionClasses::__construct()" has type "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CollisionA|Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CollisionB" but this class was not found.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\UnionClasses::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testTypeNotGuessableWithTypeSet()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('a1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register('a2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotGuessableArgument::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertCount(1, $container->getDefinition('a')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, (string) $container->getDefinition('a')->getArgument(0));
    }
    public function testWithTypeSet()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('c1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register('c2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $container->setAlias(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface::class, 'c2');
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CannotBeAutowired::class);
        $aDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertCount(1, $container->getDefinition('a')->getArguments());
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface::class, (string) $container->getDefinition('a')->getArgument(0));
    }
    /**
     * @group legacy
     * @expectedDeprecation Relying on service auto-registration for type "Symfony\Component\DependencyInjection\Tests\Compiler\Lille" is deprecated since Symfony 3.4 and won't be supported in 4.0. Create a service named "Symfony\Component\DependencyInjection\Tests\Compiler\Lille" instead.
     * @expectedDeprecation Relying on service auto-registration for type "Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas" is deprecated since Symfony 3.4 and won't be supported in 4.0. Create a service named "Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas" instead.
     */
    public function testCreateDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $coopTilleulsDefinition = $container->register('coop_tilleuls', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\LesTilleuls::class);
        $coopTilleulsDefinition->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertCount(2, $container->getDefinition('coop_tilleuls')->getArguments());
        $this->assertEquals('MolliePrefix\\autowired.Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Dunglas', $container->getDefinition('coop_tilleuls')->getArgument(0));
        $this->assertEquals('MolliePrefix\\autowired.Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Dunglas', $container->getDefinition('coop_tilleuls')->getArgument(1));
        $dunglasDefinition = $container->getDefinition('MolliePrefix\\autowired.Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Dunglas');
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class, $dunglasDefinition->getClass());
        $this->assertFalse($dunglasDefinition->isPublic());
        $this->assertCount(1, $dunglasDefinition->getArguments());
        $this->assertEquals('MolliePrefix\\autowired.Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Lille', $dunglasDefinition->getArgument(0));
        $lilleDefinition = $container->getDefinition('MolliePrefix\\autowired.Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\Lille');
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class, $lilleDefinition->getClass());
    }
    public function testResolveParameter()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('class_name', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $barDefinition = $container->register('bar', '%class_name%');
        $barDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, $container->getDefinition('bar')->getArgument(0));
    }
    public function testOptionalParameter()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $optDefinition = $container->register('opt', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\OptionalParameter::class);
        $optDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('opt');
        $this->assertNull($definition->getArgument(0));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, $definition->getArgument(1));
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, $definition->getArgument(2));
    }
    /**
     * @requires PHP 8
     */
    public function testParameterWithNullUnionIsSkipped()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $optDefinition = $container->register('opt', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\UnionNull::class);
        $optDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('opt');
        $this->assertNull($definition->getArgument(0));
    }
    /**
     * @requires PHP 8
     */
    public function testParameterWithNullUnionIsAutowired()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $optDefinition = $container->register('opt', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\UnionNull::class);
        $optDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('opt');
        $this->assertEquals(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface::class, $definition->getArgument(0));
    }
    public function testDontTriggerAutowiring()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertCount(0, $container->getDefinition('bar')->getArguments());
    }
    public function testClassNotFoundThrowsException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "a": argument "$r" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\BadTypeHintedArgument::__construct()" has type "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotARealClass" but this class was not found.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\BadTypeHintedArgument::class);
        $aDefinition->setAutowired(\true);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testParentClassNotFoundThrowsException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessageMatches('{^Cannot autowire service "a": argument "\\$r" of method "(Symfony\\\\Component\\\\DependencyInjection\\\\Tests\\\\Compiler\\\\)BadParentTypeHintedArgument::__construct\\(\\)" has type "\\1OptionalServiceClass" but this class is missing a parent class \\(Class "?Symfony\\\\Bug\\\\NotExistClass"? not found}');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aDefinition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\BadParentTypeHintedArgument::class);
        $aDefinition->setAutowired(\true);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. You should rename (or alias) the "foo" service to "Symfony\Component\DependencyInjection\Tests\Compiler\Foo" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\AutowiringFailedException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "bar": argument "$foo" of method "Symfony\Component\DependencyInjection\Tests\Compiler\Bar::__construct()" references class "Symfony\Component\DependencyInjection\Tests\Compiler\Foo" but this service is abstract. You should maybe alias this class to the existing "foo" service.
     */
    public function testDontUseAbstractServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class)->setAbstract(\true);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    public function testSomeSpecificArgumentsAreSet()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class);
        $container->register('multiple', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArguments::class)->setAutowired(\true)->setArguments([1 => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), 3 => ['bar']]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('multiple');
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArguments::class), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArguments::class), ['bar']], $definition->getArguments());
    }
    public function testScalarArgsCannotBeAutowired()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "arg_no_type_hint": argument "$bar" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\MultipleArguments::__construct()" is type-hinted "array", you should configure its value explicitly.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class);
        $container->register('arg_no_type_hint', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArguments::class)->setArguments([1 => 'foo'])->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    /**
     * @requires PHP 8
     */
    public function testUnionScalarArgsCannotBeAutowired()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "union_scalars": argument "$timeout" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\UnionScalars::__construct()" is type-hinted "int|float", you should configure its value explicitly.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('union_scalars', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\UnionScalars::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    public function testNoTypeArgsCannotBeAutowired()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "arg_no_type_hint": argument "$foo" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\MultipleArguments::__construct()" has no type-hint, you should configure its value explicitly.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas::class);
        $container->register('arg_no_type_hint', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArguments::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    /**
     * @requires PHP < 8
     */
    public function testOptionalScalarNotReallyOptionalUsesDefaultValue()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class);
        $definition = $container->register('not_really_optional_scalar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes\MultipleArgumentsOptionalScalarNotReallyOptional::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertSame('default_val', $definition->getArgument(1));
    }
    public function testOptionalScalarArgsDontMessUpOrder()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class);
        $container->register('with_optional_scalar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalar::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('with_optional_scalar');
        $this->assertEquals([
            new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalar::class),
            // use the default value
            'default_val',
            new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class),
        ], $definition->getArguments());
    }
    public function testOptionalScalarArgsNotPassedIfLast()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class);
        $container->register('with_optional_scalar_last', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalarLast::class)->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('with_optional_scalar_last');
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalarLast::class), new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalarLast::class)], $definition->getArguments());
    }
    public function testOptionalArgsNoRequiredForCoreClasses()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \SplFileObject::class)->addArgument('foo.txt')->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $definition = $container->getDefinition('foo');
        $this->assertEquals(['foo.txt'], $definition->getArguments());
    }
    public function testSetterInjection()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        // manually configure *one* call, to override autowiring
        $container->register('setter_injection', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)->setAutowired(\true)->addMethodCall('setWithCallsConfigured', ['manual_arg1', 'manual_arg2']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(['setWithCallsConfigured', 'setFoo', 'setDependencies', 'setChildMethodWithoutDocBlock'], \array_column($methodCalls, 0));
        // test setWithCallsConfigured args
        $this->assertEquals(['manual_arg1', 'manual_arg2'], $methodCalls[0][1]);
        // test setFoo args
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)], $methodCalls[1][1]);
    }
    public function testWithNonExistingSetterAndAutowiring()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CaseSensitiveClass": method "setLogger()" does not exist.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class)->setAutowired(\true);
        $definition->addMethodCall('setLogger');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    public function testExplicitMethodInjection()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $container->register('setter_injection', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)->setAutowired(\true)->addMethodCall('notASetter', []);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(['notASetter', 'setFoo', 'setDependencies', 'setWithCallsConfigured', 'setChildMethodWithoutDocBlock'], \array_column($methodCalls, 0));
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)], $methodCalls[0][1]);
    }
    /**
     * @group legacy
     * @expectedDeprecation Relying on service auto-registration for type "Symfony\Component\DependencyInjection\Tests\Compiler\A" is deprecated since Symfony 3.4 and won't be supported in 4.0. Create a service named "Symfony\Component\DependencyInjection\Tests\Compiler\A" instead.
     */
    public function testTypedReference()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)->setProperty('a', [new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, $container->getDefinition('autowired.' . \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class)->getClass());
    }
    /**
     * @dataProvider getCreateResourceTests
     * @group legacy
     */
    public function testCreateResourceForClass($className, $isEqual)
    {
        $startingResource = \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass::createResourceForClass(new \ReflectionClass(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ClassForResource::class));
        $newResource = \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass::createResourceForClass(new \ReflectionClass(__NAMESPACE__ . '\\' . $className));
        // hack so the objects don't differ by the class name
        $startingReflObject = new \ReflectionObject($startingResource);
        $reflProp = $startingReflObject->getProperty('class');
        $reflProp->setAccessible(\true);
        $reflProp->setValue($startingResource, __NAMESPACE__ . '\\' . $className);
        if ($isEqual) {
            $this->assertEquals($startingResource, $newResource);
        } else {
            $this->assertNotEquals($startingResource, $newResource);
        }
    }
    public function getCreateResourceTests()
    {
        return [['IdenticalClassResource', \true], ['ClassChangedConstructorArgs', \false]];
    }
    public function testIgnoreServiceWithClassNotExisting()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('class_not_exist', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\OptionalServiceClass::class);
        $barDefinition = $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class);
        $barDefinition->setAutowired(\true);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertTrue($container->hasDefinition('bar'));
    }
    public function testSetterInjectionCollisionThrowsException()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('c1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register('c2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $aDefinition = $container->register('setter_injection_collision', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjectionCollision::class);
        $aDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        try {
            $pass->process($container);
        } catch (\MolliePrefix\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException $e) {
        }
        $this->assertNotNull($e);
        $this->assertSame('Cannot autowire service "setter_injection_collision": argument "$collision" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\SetterInjectionCollision::setMultipleInstancesForOneArg()" references interface "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\CollisionInterface" but no such service exists. You should maybe alias this interface to one of these existing services: "c1", "c2".', $e->getMessage());
    }
    public function testInterfaceWithNoImplementationSuggestToWriteOne()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "my_service": argument "$i" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\K::__construct()" references interface "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\IInterface" but no such service exists. Did you create a class that implements this interface?');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $aDefinition = $container->register('my_service', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\K::class);
        $aDefinition->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. You should rename (or alias) the "foo" service to "Symfony\Component\DependencyInjection\Tests\Compiler\Foo" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\AutowiringFailedException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "bar": argument "$foo" of method "Symfony\Component\DependencyInjection\Tests\Compiler\Bar::__construct()" references class "Symfony\Component\DependencyInjection\Tests\Compiler\Foo" but no such service exists. You should maybe alias this class to the existing "foo" service.
     */
    public function testProcessDoesNotTriggerDeprecations()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('deprecated', 'MolliePrefix\\Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\DeprecatedClass')->setDeprecated(\true);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertTrue($container->hasDefinition('deprecated'));
        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
    }
    public function testEmptyStringIsKept()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalar::class)->setAutowired(\true)->setArguments(['', '']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\MultipleArgumentsOptionalScalar::class), '', new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille::class)], $container->getDefinition('foo')->getArguments());
    }
    public function testWithFactory()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $definition = $container->register('a', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class)->setFactory([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, 'create'])->setAutowired(\true);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class)], $definition->getArguments());
    }
    /**
     * @dataProvider provideNotWireableCalls
     */
    public function testNotWireableCalls($method, $expectedMsg)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $foo = $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotWireable::class)->setAutowired(\true)->addMethodCall('setBar', [])->addMethodCall('setOptionalNotAutowireable', [])->addMethodCall('setOptionalNoTypeHint', [])->addMethodCall('setOptionalArgNoAutowireable', []);
        if ($method) {
            $foo->addMethodCall($method, []);
        }
        $this->expectException(\MolliePrefix\Symfony\Component\DependencyInjection\Exception\RuntimeException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass())->process($container);
    }
    public function provideNotWireableCalls()
    {
        return [['setNotAutowireable', 'Cannot autowire service "foo": argument "$n" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotWireable::setNotAutowireable()" has type "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotARealClass" but this class was not found.'], ['setDifferentNamespace', 'Cannot autowire service "foo": argument "$n" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotWireable::setDifferentNamespace()" references class "stdClass" but no such service exists. It cannot be auto-registered because it is from a different root namespace.'], [null, 'Invalid service "foo": method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\NotWireable::setProtectedMethod()" must be public.']];
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. Try changing the type-hint for argument "$i" of method "Symfony\Component\DependencyInjection\Tests\Compiler\J::__construct()" to "Symfony\Component\DependencyInjection\Tests\Compiler\IInterface" instead.
     * @expectedExceptionInSymfony4 \Symfony\Component\DependencyInjection\Exception\AutowiringFailedException
     * @expectedExceptionMessageInSymfony4 Cannot autowire service "j": argument "$i" of method "Symfony\Component\DependencyInjection\Tests\Compiler\J::__construct()" references class "Symfony\Component\DependencyInjection\Tests\Compiler\I" but no such service exists. Try changing the type-hint to "Symfony\Component\DependencyInjection\Tests\Compiler\IInterface" instead.
     */
    public function testByIdAlternative()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface::class, 'i');
        $container->register('i', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I::class);
        $container->register('j', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\J::class)->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    /**
     * @group legacy
     * @expectedDeprecation Autowiring services based on the types they implement is deprecated since Symfony 3.3 and won't be supported in version 4.0. Try changing the type-hint for "Symfony\Component\DependencyInjection\Tests\Compiler\A" in "Symfony\Component\DependencyInjection\Tests\Compiler\Bar" to "Symfony\Component\DependencyInjection\Tests\Compiler\AInterface" instead.
     */
    public function testTypedReferenceDeprecationNotice()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('aClass', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->setAlias(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\AInterface::class, 'aClass');
        $container->register('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)->setProperty('a', [new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar::class)]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testExceptionWhenAliasExists()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "j": argument "$i" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\J::__construct()" references class "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\I" but no such service exists. Try changing the type-hint to "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\IInterface" instead.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        // multiple I services... but there *is* IInterface available
        $container->setAlias(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface::class, 'i');
        $container->register('i', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I::class);
        $container->register('i2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I::class);
        // J type-hints against I concretely
        $container->register('j', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\J::class)->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testExceptionWhenAliasDoesNotExist()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\AutowiringFailedException');
        $this->expectExceptionMessage('Cannot autowire service "j": argument "$i" of method "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\J::__construct()" references class "Symfony\\Component\\DependencyInjection\\Tests\\Compiler\\I" but no such service exists. You should maybe alias this class to one of these existing services: "i", "i2".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        // multiple I instances... but no IInterface alias
        $container->register('i', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I::class);
        $container->register('i2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I::class);
        // J type-hints against I concretely
        $container->register('j', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\J::class)->setAutowired(\true);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
    }
    public function testInlineServicesAreNotCandidates()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\XmlFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(\realpath(__DIR__ . '/../Fixtures/xml')));
        $loader->load('services_inline_not_candidate.xml');
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowirePass();
        $pass->process($container);
        $this->assertSame([], $container->getDefinition('autowired')->getArguments());
    }
}
