<?php

namespace MolliePrefix;

/**
 * Represents foo.
 */
class Foo
{
}
/**
 * Represents foo.
 */
\class_alias('MolliePrefix\\Foo', 'MolliePrefix\\Foo', \false);
/**
 * @param mixed $bar
 */
function &foo($bar)
{
    $baz = function () {
    };
    $a = \true ? \true : \false;
    $b = "{$a}";
    $c = "{$b}";
}
