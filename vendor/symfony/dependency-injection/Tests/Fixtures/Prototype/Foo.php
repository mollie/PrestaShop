<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;

class Foo implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface
{
    public function __construct($bar = null)
    {
    }
    public function setFoo(self $foo)
    {
    }
}
