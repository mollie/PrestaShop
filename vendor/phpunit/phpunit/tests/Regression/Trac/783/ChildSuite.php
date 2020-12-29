<?php

namespace MolliePrefix;

require_once 'OneTest.php';
require_once 'TwoTest.php';
class ChildSuite
{
    public static function suite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('Child');
        $suite->addTestSuite('OneTest');
        $suite->addTestSuite('TwoTest');
        return $suite;
    }
}
\class_alias('MolliePrefix\\ChildSuite', 'ChildSuite', \false);
