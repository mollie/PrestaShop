<?php

namespace MolliePrefix;

class DependencyTestSuite
{
    public static function suite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('Test Dependencies');
        $suite->addTestSuite('DependencySuccessTest');
        $suite->addTestSuite('DependencyFailureTest');
        return $suite;
    }
}
\class_alias('MolliePrefix\\DependencyTestSuite', 'DependencyTestSuite', \false);
