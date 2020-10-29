<?php

namespace MolliePrefix;

class ChangeCurrentWorkingDirectoryTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testSomethingThatChangesTheCwd()
    {
        \chdir('../');
        $this->assertTrue(\true);
    }
}
\class_alias('MolliePrefix\\ChangeCurrentWorkingDirectoryTest', 'MolliePrefix\\ChangeCurrentWorkingDirectoryTest', \false);
