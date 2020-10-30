<?php

namespace MolliePrefix;

class Struct
{
    public $var;
    public function __construct($var)
    {
        $this->var = $var;
    }
}
\class_alias('MolliePrefix\\Struct', 'MolliePrefix\\Struct', \false);
