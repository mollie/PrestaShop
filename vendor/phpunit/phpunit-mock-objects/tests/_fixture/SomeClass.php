<?php

namespace MolliePrefix;

class SomeClass
{
    public function doSomething($a, $b)
    {
        return;
    }
    public function doSomethingElse($c)
    {
        return;
    }
}
\class_alias('MolliePrefix\\SomeClass', 'SomeClass', \false);
