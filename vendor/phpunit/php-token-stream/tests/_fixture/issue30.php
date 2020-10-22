<?php

namespace MolliePrefix;

class Foo
{
    public function bar()
    {
        return \MolliePrefix\Foo::CLASS;
    }
}
\class_alias('MolliePrefix\\Foo', 'Foo', \false);
