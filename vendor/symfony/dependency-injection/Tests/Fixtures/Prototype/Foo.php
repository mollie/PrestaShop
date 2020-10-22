<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;

class Foo implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface
{
    public function __construct($bar = null)
    {
    }
    public function setFoo(self $foo)
    {
    }
}
