<?php

namespace MolliePrefix;

if ($neverHappens) {
    // @codeCoverageIgnoreStart
    print '*';
    // @codeCoverageIgnoreEnd
}
/**
 * @codeCoverageIgnore
 */
class Foo
{
    public function bar()
    {
    }
}
/**
 * @codeCoverageIgnore
 */
\class_alias('MolliePrefix\\Foo', 'MolliePrefix\\Foo', \false);
class Bar
{
    /**
     * @codeCoverageIgnore
     */
    public function foo()
    {
    }
}
\class_alias('MolliePrefix\\Bar', 'MolliePrefix\\Bar', \false);
function baz()
{
    print '*';
    // @codeCoverageIgnore
}
interface Bor
{
    public function foo();
}
\class_alias('MolliePrefix\\Bor', 'MolliePrefix\\Bor', \false);
