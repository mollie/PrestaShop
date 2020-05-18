<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface;

class Foo implements FooInterface, BarInterface
{
    public function __construct($bar = null)
    {
    }
    public function setFoo(self $foo)
    {
    }
}
