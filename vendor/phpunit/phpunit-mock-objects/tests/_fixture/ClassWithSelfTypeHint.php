<?php

namespace MolliePrefix;

class ClassWithSelfTypeHint
{
    public function foo(self $foo)
    {
    }
}
\class_alias('MolliePrefix\\ClassWithSelfTypeHint', 'MolliePrefix\\ClassWithSelfTypeHint', \false);
