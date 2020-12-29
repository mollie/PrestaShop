<?php

namespace MolliePrefix;

class Issue503Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testCompareDifferentLineEndings()
    {
        $this->assertSame("foo\n", "foo\r\n");
    }
}
\class_alias('MolliePrefix\\Issue503Test', 'Issue503Test', \false);
