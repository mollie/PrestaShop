<?php

namespace MolliePrefix;

class Test
{
    public function methodOne()
    {
        $foo = new class
        {
            public function method_in_anonymous_class()
            {
                return \true;
            }
        };
        return $foo->method_in_anonymous_class();
    }
    public function methodTwo()
    {
        return \false;
    }
}
\class_alias('MolliePrefix\\Test', 'Test', \false);
