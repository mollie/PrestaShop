<?php

namespace MolliePrefix;

require_once 'ChildSuite.php';
class ParentSuite
{
    public static function suite()
    {
        $suite = new \MolliePrefix\PHPUnit_Framework_TestSuite('Parent');
        $suite->addTest(\MolliePrefix\ChildSuite::suite());
        return $suite;
    }
}
\class_alias('MolliePrefix\\ParentSuite', 'ParentSuite', \false);
