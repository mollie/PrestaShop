<?php

namespace MolliePrefix;

/**
 * Some comment
 */
class Foo
{
    function foo()
    {
    }
    /**
     * @param Baz $baz
     */
    public function bar(\MolliePrefix\Baz $baz)
    {
    }
    /**
     * @param Foobar $foobar
     */
    public static function foobar(\MolliePrefix\Foobar $foobar)
    {
    }
    public function barfoo(\MolliePrefix\Barfoo $barfoo)
    {
    }
    /**
     * This docblock does not belong to the baz function
     */
    public function baz()
    {
    }
    public function blaz($x, $y)
    {
    }
}
/**
 * Some comment
 */
\class_alias('MolliePrefix\\Foo', 'MolliePrefix\\Foo', \false);
