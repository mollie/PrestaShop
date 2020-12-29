<?php

namespace MolliePrefix;

class Bar
{
    public function doSomethingElse()
    {
        return 'result';
    }
}
\class_alias('MolliePrefix\\Bar', 'Bar', \false);
