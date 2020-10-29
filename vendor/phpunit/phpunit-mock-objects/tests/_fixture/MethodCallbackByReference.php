<?php

namespace MolliePrefix;

class MethodCallbackByReference
{
    public function bar(&$a, &$b, $c)
    {
        \MolliePrefix\Legacy::bar($a, $b, $c);
    }
    public function callback(&$a, &$b, $c)
    {
        $b = 1;
    }
}
\class_alias('MolliePrefix\\MethodCallbackByReference', 'MolliePrefix\\MethodCallbackByReference', \false);
