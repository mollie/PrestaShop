<?php

namespace MolliePrefix;

class ClassWithScalarTypeDeclarations
{
    public function foo(string $string, int $int)
    {
    }
}
\class_alias('MolliePrefix\\ClassWithScalarTypeDeclarations', 'ClassWithScalarTypeDeclarations', \false);
