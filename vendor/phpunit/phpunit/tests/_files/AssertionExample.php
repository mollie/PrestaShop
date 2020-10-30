<?php

namespace MolliePrefix;

class AssertionExample
{
    public function doSomething()
    {
        \assert(\false);
    }
}
\class_alias('MolliePrefix\\AssertionExample', 'MolliePrefix\\AssertionExample', \false);
