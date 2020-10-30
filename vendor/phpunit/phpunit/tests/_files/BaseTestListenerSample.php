<?php

namespace MolliePrefix;

class BaseTestListenerSample extends \MolliePrefix\PHPUnit_Framework_BaseTestListener
{
    public $endCount = 0;
    public function endTest(\MolliePrefix\PHPUnit_Framework_Test $test, $time)
    {
        $this->endCount++;
    }
}
\class_alias('MolliePrefix\\BaseTestListenerSample', 'MolliePrefix\\BaseTestListenerSample', \false);
