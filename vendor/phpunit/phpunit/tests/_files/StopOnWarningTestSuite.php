<?php

namespace MolliePrefix;

class StopOnWarningTestSuite
{
    public static function suite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('Test Warnings');
        $suite->addTestSuite('NoTestCases');
        $suite->addTestSuite('CoverageClassTest');
        return $suite;
    }
}
\class_alias('MolliePrefix\\StopOnWarningTestSuite', 'MolliePrefix\\StopOnWarningTestSuite', \false);
