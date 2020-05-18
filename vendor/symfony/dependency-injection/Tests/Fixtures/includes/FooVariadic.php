<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\Foo;
class FooVariadic
{
    public function __construct(Foo $foo)
    {
    }
    public function bar(...$arguments)
    {
    }
}
