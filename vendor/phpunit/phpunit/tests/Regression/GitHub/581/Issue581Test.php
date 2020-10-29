<?php

namespace MolliePrefix;

class Issue581Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testExportingObjectsDoesNotBreakWindowsLineFeeds()
    {
        $this->assertEquals((object) [1, 2, "Test\r\n", 4, 5, 6, 7, 8], (object) [1, 2, "Test\r\n", 4, 1, 6, 7, 8]);
    }
}
\class_alias('MolliePrefix\\Issue581Test', 'MolliePrefix\\Issue581Test', \false);
