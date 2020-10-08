<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo;
class FooVariadic
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
    public function bar(...$arguments)
    {
    }
}
