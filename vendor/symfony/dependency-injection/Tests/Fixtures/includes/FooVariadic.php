<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\Foo;
class FooVariadic
{
    public function __construct(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
    public function bar(...$arguments)
    {
    }
}
