<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille;
class MultipleArgumentsOptionalScalarNotReallyOptional
{
    public function __construct(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A $a, $foo = 'default_val', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Lille $lille)
    {
    }
}
