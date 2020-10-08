<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

if (\PHP_VERSION_ID >= 80000) {
    require __DIR__ . '/uniontype_classes.php';
}
class Foo
{
}
class Bar
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
}
interface AInterface
{
}
class A implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\AInterface
{
    public static function create(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
}
class B extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A
{
}
class C
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
interface DInterface
{
}
interface EInterface extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface
{
}
interface IInterface
{
}
class I implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface
{
}
class F extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\EInterface
{
}
class G
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\EInterface $e, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface $i)
    {
    }
}
class H
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\B $b, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d)
    {
    }
}
class D
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d)
    {
    }
}
class E
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\D $d = null)
    {
    }
}
class J
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\I $i)
    {
    }
}
class K
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface $i)
    {
    }
}
interface CollisionInterface
{
}
class CollisionA implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface
{
}
class CollisionB implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface
{
}
class CannotBeAutowired
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $collision)
    {
    }
}
class Lille
{
}
class Dunglas
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $l)
    {
    }
}
class LesTilleuls
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $j, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k)
    {
    }
}
class OptionalParameter
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $c = null, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $f = null)
    {
    }
}
class BadTypeHintedArgument
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $r)
    {
    }
}
class BadParentTypeHintedArgument
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\OptionalServiceClass $r)
    {
    }
}
class NotGuessableArgument
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $k)
    {
    }
}
class NotGuessableArgumentForSubclass
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $k)
    {
    }
}
class MultipleArguments
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $k, $foo, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $dunglas, array $bar)
    {
    }
}
class MultipleArgumentsOptionalScalar
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, $foo = 'default_val', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille = null)
    {
    }
}
class MultipleArgumentsOptionalScalarLast
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille, $foo = 'some_val')
    {
    }
}
/*
 * Classes used for testing createResourceForClass
 */
class ClassForResource
{
    public function __construct($foo, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar = null)
    {
    }
    public function setBar(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar)
    {
    }
}
class IdenticalClassResource extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ClassForResource
{
}
class ClassChangedConstructorArgs extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ClassForResource
{
    public function __construct($foo, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar, $baz)
    {
    }
}
class SetterInjectionCollision
{
    /**
     * @required
     */
    public function setMultipleInstancesForOneArg(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $collision)
    {
        // The CollisionInterface cannot be autowired - there are multiple
        // should throw an exception
    }
}
class SetterInjection extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjectionParent
{
    /**
     * @required
     */
    public function setFoo(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
        // should be called
    }
    /** @inheritdoc*/
    // <- brackets are missing on purpose
    public function setDependencies(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called
    }
    /** {@inheritdoc} */
    public function setWithCallsConfigured(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // this method has a calls configured on it
    }
    public function notASetter(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called only when explicitly specified
    }
    /**
     * @required*/
    public function setChildMethodWithoutDocBlock(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
class SetterInjectionParent
{
    /** @required*/
    public function setDependencies(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called
    }
    public function notASetter(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // @required should be ignored when the child does not add @inheritdoc
    }
    /**	@required <tab> prefix is on purpose */
    public function setWithCallsConfigured(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
    /** @required */
    public function setChildMethodWithoutDocBlock(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
class NotWireable
{
    public function setNotAutowireable(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $n)
    {
    }
    public function setBar()
    {
    }
    public function setOptionalNotAutowireable(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $n = null)
    {
    }
    public function setDifferentNamespace(\stdClass $n)
    {
    }
    public function setOptionalNoTypeHint($foo = null)
    {
    }
    public function setOptionalArgNoAutowireable($other = 'default_val')
    {
    }
    /** @required */
    protected function setProtectedMethod(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
class PrivateConstructor
{
    private function __construct()
    {
    }
}
class ScalarSetter
{
    /**
     * @required
     */
    public function setDefaultLocale($defaultLocale)
    {
    }
}
