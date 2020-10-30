<?php

namespace MolliePrefix;

interface foo
{
}
\class_alias('MolliePrefix\\foo', 'MolliePrefix\\foo', \false);
class class_with_method_that_declares_anonymous_class
{
    public function method()
    {
        $o = new class
        {
            public function foo()
            {
            }
        };
        $o = new class
        {
            public function foo()
            {
            }
        };
        $o = new class extends \stdClass
        {
        };
        $o = new class extends \stdClass
        {
        };
        $o = new class implements \MolliePrefix\foo
        {
        };
    }
}
\class_alias('MolliePrefix\\class_with_method_that_declares_anonymous_class', 'MolliePrefix\\class_with_method_that_declares_anonymous_class', \false);
