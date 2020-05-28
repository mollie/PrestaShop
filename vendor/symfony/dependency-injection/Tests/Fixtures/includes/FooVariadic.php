<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler\Foo;
class FooVariadic
{
    public function __construct(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler\Foo $foo)
    {
    }
    public function bar(...$arguments)
    {
    }
}
