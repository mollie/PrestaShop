<?php

namespace MolliePrefix;

class StaticMockTestClass
{
    public static function doSomething()
    {
    }
    public static function doSomethingElse()
    {
        return static::doSomething();
    }
}
\class_alias('MolliePrefix\\StaticMockTestClass', 'MolliePrefix\\StaticMockTestClass', \false);
