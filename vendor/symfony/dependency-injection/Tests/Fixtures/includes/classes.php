<?php

namespace _PhpScoper5ece82d7231e4;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface as ProxyDumper;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
function sc_configure($instance)
{
    $instance->configure();
}
class BarClass extends \_PhpScoper5ece82d7231e4\BazClass
{
    protected $baz;
    public $foo = 'foo';
    public function setBaz(\_PhpScoper5ece82d7231e4\BazClass $baz)
    {
        $this->baz = $baz;
    }
    public function getBaz()
    {
        return $this->baz;
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\BarClass', 'BarClass', \false);
class BazClass
{
    protected $foo;
    public function setFoo(\_PhpScoper5ece82d7231e4\Foo $foo)
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
\class_alias('_PhpScoper5ece82d7231e4\\BazClass', 'BazClass', \false);
class BarUserClass
{
    public $bar;
    public function __construct(\_PhpScoper5ece82d7231e4\BarClass $bar)
    {
        $this->bar = $bar;
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\BarUserClass', 'BarUserClass', \false);
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
\class_alias('_PhpScoper5ece82d7231e4\\MethodCallClass', 'MethodCallClass', \false);
class DummyProxyDumper implements \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface
{
    public function isProxyCandidate(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return $definition->isLazy();
    }
    public function getProxyFactoryCode(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition $definition, $id, $factoryCall = null)
    {
        return "        // lazy factory for {$definition->getClass()}\n\n";
    }
    public function getProxyCode(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition $definition)
    {
        return "// proxy code for {$definition->getClass()}\n";
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\DummyProxyDumper', 'DummyProxyDumper', \false);
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
\class_alias('_PhpScoper5ece82d7231e4\\LazyContext', 'LazyContext', \false);
class FoobarCircular
{
    public function __construct(\_PhpScoper5ece82d7231e4\FooCircular $foo)
    {
        $this->foo = $foo;
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\FoobarCircular', 'FoobarCircular', \false);
class FooCircular
{
    public function __construct(\_PhpScoper5ece82d7231e4\BarCircular $bar)
    {
        $this->bar = $bar;
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\FooCircular', 'FooCircular', \false);
class BarCircular
{
    public function addFoobar(\_PhpScoper5ece82d7231e4\FoobarCircular $foobar)
    {
        $this->foobar = $foobar;
    }
}
\class_alias('_PhpScoper5ece82d7231e4\\BarCircular', 'BarCircular', \false);
