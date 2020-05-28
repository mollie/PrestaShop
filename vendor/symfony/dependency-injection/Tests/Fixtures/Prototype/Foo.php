<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;

class Foo implements \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface
{
    public function __construct($bar = null)
    {
    }
    public function setFoo(self $foo)
    {
    }
}
