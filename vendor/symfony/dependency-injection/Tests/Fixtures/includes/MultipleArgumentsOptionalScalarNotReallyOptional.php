<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\A;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\Lille;
class MultipleArgumentsOptionalScalarNotReallyOptional
{
    public function __construct(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, $foo = 'default_val', \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille)
    {
    }
}
