<?php

namespace MolliePrefix;

class Foo
{
    public function doSomething(\MolliePrefix\Bar $bar)
    {
        return $bar->doSomethingElse();
    }
}
\class_alias('MolliePrefix\\Foo', 'MolliePrefix\\Foo', \false);
