<?php

namespace MolliePrefix;

/** Docblock */
interface Foo
{
    public function bar();
}
/** Docblock */
\class_alias('MolliePrefix\\Foo', 'Foo', \false);
class Foo
{
    public function bar()
    {
    }
}
\class_alias('MolliePrefix\\Foo', 'Foo', \false);
function baz()
{
    // a one-line comment
    print '*';
    // a one-line comment
    /* a one-line comment */
    print '*';
    /* a one-line comment */
    /* a one-line comment
     */
    print '*';
    /* a one-line comment
     */
    print '*';
    // @codeCoverageIgnore
    print '*';
    // @codeCoverageIgnoreStart
    print '*';
    print '*';
    // @codeCoverageIgnoreEnd
    print '*';
}
