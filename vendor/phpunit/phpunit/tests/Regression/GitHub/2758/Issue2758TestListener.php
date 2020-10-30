<?php

namespace MolliePrefix;

class Issue2758TestListener extends \MolliePrefix\PHPUnit_Framework_BaseTestListener
{
    public function endTest(\MolliePrefix\PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof \MolliePrefix\PHPUnit_Framework_TestCase) {
            return;
        }
        $test->addToAssertionCount(1);
    }
}
\class_alias('MolliePrefix\\Issue2758TestListener', 'MolliePrefix\\Issue2758TestListener', \false);
