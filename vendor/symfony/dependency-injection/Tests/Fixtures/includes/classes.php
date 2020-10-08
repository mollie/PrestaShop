<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface as ProxyDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
function sc_configure($instance)
{
    $instance->configure();
}
class BarClass extends \MolliePrefix\BazClass
{
    protected $baz;
    public $foo = 'foo';
    public function setBaz(\MolliePrefix\BazClass $baz)
    {
        $this->baz = $baz;
    }
    public function getBaz()
    {
        return $this->baz;
    }
}
\class_alias('MolliePrefix\\BarClass', 'BarClass', \false);
class BazClass
{
    protected $foo;
    public function setFoo(\MolliePrefix\Foo $foo)
    {
        $this->foo = $foo;
    }
    public function configure($instance)
    {
        $instance->configure();
    }
    public static function getInstance()
    {
        return new self();
    }
    public static function configureStatic($instance)
    {
        $instance->configure();
    }
    public static function configureStatic1()
    {
    }
}
\class_alias('MolliePrefix\\BazClass', 'BazClass', \false);
class BarUserClass
{
    public $bar;
    public function __construct(\MolliePrefix\BarClass $bar)
    {
        $this->bar = $bar;
    }
}
\class_alias('MolliePrefix\\BarUserClass', 'BarUserClass', \false);
class MethodCallClass
{
    public $simple;
    public $complex;
    private $callPassed = \false;
    public function callMe()
    {
        $this->callPassed = \is_scalar($this->simple) && \is_object($this->complex);
    }
    public function callPassed()
    {
        return $this->callPassed;
    }
}
\class_alias('MolliePrefix\\MethodCallClass', 'MethodCallClass', \false);
class DummyProxyDumper implements \MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface
{
    public function isProxyCandidate(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return $definition->isLazy();
    }
    public function getProxyFactoryCode(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition, $id, $factoryCall = null)
    {
        return "        // lazy factory for {$definition->getClass()}\n\n";
    }
    public function getProxyCode(\MolliePrefix\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return "// proxy code for {$definition->getClass()}\n";
    }
}
\class_alias('MolliePrefix\\DummyProxyDumper', 'DummyProxyDumper', \false);
class LazyContext
{
    public $lazyValues;
    public $lazyEmptyValues;
    public function __construct($lazyValues, $lazyEmptyValues)
    {
        $this->lazyValues = $lazyValues;
        $this->lazyEmptyValues = $lazyEmptyValues;
    }
}
\class_alias('MolliePrefix\\LazyContext', 'LazyContext', \false);
class FoobarCircular
{
    public function __construct(\MolliePrefix\FooCircular $foo)
    {
        $this->foo = $foo;
    }
}
\class_alias('MolliePrefix\\FoobarCircular', 'FoobarCircular', \false);
class FooCircular
{
    public function __construct(\MolliePrefix\BarCircular $bar)
    {
        $this->bar = $bar;
    }
}
\class_alias('MolliePrefix\\FooCircular', 'FooCircular', \false);
class BarCircular
{
    public function addFoobar(\MolliePrefix\FoobarCircular $foobar)
    {
        $this->foobar = $foobar;
    }
}
\class_alias('MolliePrefix\\BarCircular', 'BarCircular', \false);
