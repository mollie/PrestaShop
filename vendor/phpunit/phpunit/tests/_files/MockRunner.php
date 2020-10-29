<?php

namespace MolliePrefix;

class MockRunner extends \MolliePrefix\PHPUnit_Runner_BaseTestRunner
{
    protected function runFailed($message)
    {
    }
}
\class_alias('MolliePrefix\\MockRunner', 'MolliePrefix\\MockRunner', \false);
