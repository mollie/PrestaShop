<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

class Foo
{
}
class Bar
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
}
interface AInterface
{
}
class A implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\AInterface
{
    public static function create(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
}
class B extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A
{
}
class C
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
interface DInterface
{
}
interface EInterface extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface
{
}
interface IInterface
{
}
class I implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface
{
}
class F extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\I implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\EInterface
{
}
class G
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\EInterface $e, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface $i)
    {
    }
}
class H
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\B $b, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d)
    {
    }
}
class D
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\DInterface $d)
    {
    }
}
class E
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\D $d = null)
    {
    }
}
class J
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\I $i)
    {
    }
}
class K
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\IInterface $i)
    {
    }
}
interface CollisionInterface
{
}
class CollisionA implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface
{
}
class CollisionB implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface
{
}
class CannotBeAutowired
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $collision)
    {
    }
}
class Lille
{
}
class Dunglas
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $l)
    {
    }
}
class LesTilleuls
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $j, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k)
    {
    }
}
class OptionalParameter
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $c = null, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $f = null)
    {
    }
}
class BadTypeHintedArgument
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $r)
    {
    }
}
class BadParentTypeHintedArgument
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $k, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\OptionalServiceClass $r)
    {
    }
}
class NotGuessableArgument
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $k)
    {
    }
}
class NotGuessableArgumentForSubclass
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $k)
    {
    }
}
class MultipleArguments
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $k, $foo, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Dunglas $dunglas, array $bar)
    {
    }
}
class MultipleArgumentsOptionalScalar
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, $foo = 'default_val', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille = null)
    {
    }
}
class MultipleArgumentsOptionalScalarLast
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille, $foo = 'some_val')
    {
    }
}
class MultipleArgumentsOptionalScalarNotReallyOptional
{
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, $foo = 'default_val', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille)
    {
    }
}
/*
 * Classes used for testing createResourceForClass
 */
class ClassForResource
{
    public function __construct($foo, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar = null)
    {
    }
    public function setBar(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar)
    {
    }
}
class IdenticalClassResource extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\ClassForResource
{
}
class ClassChangedConstructorArgs extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\ClassForResource
{
    public function __construct($foo, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Bar $bar, $baz)
    {
    }
}
class SetterInjectionCollision
{
    /**
     * @required
     */
    public function setMultipleInstancesForOneArg(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface $collision)
    {
        // The CollisionInterface cannot be autowired - there are multiple
        // should throw an exception
    }
}
class SetterInjection extends \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjectionParent
{
    /**
     * @required
     */
    public function setFoo(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
        // should be called
    }
    /** @inheritdoc*/
    // <- brackets are missing on purpose
    public function setDependencies(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called
    }
    /** {@inheritdoc} */
    public function setWithCallsConfigured(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // this method has a calls configured on it
    }
    public function notASetter(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called only when explicitly specified
    }
    /**
     * @required*/
    public function setChildMethodWithoutDocBlock(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
class SetterInjectionParent
{
    /** @required*/
    public function setDependencies(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // should be called
    }
    public function notASetter(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
        // @required should be ignored when the child does not add @inheritdoc
    }
    /**	@required <tab> prefix is on purpose */
    public function setWithCallsConfigured(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
    /** @required */
    public function setChildMethodWithoutDocBlock(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
    {
    }
}
class NotWireable
{
    public function setNotAutowireable(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $n)
    {
    }
    public function setBar()
    {
    }
    public function setOptionalNotAutowireable(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NotARealClass $n = null)
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
    protected function setProtectedMethod(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\A $a)
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
