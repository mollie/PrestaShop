<?php

namespace MolliePrefix;

class SampleClass
{
    public $a;
    protected $b;
    protected $c;
    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}
\class_alias('MolliePrefix\\SampleClass', 'SampleClass', \false);
