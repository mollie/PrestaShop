<?php

namespace MolliePrefix;

class IncompleteTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testIncomplete()
    {
        $this->markTestIncomplete('Test incomplete');
    }
}
\class_alias('MolliePrefix\\IncompleteTest', 'MolliePrefix\\IncompleteTest', \false);
