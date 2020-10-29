<?php

namespace MolliePrefix;

class DataProviderTestDoxTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @testdox Does something with
     */
    public function testOne()
    {
    }
    /**
     * @dataProvider provider
     */
    public function testDoesSomethingElseWith()
    {
    }
    public function provider()
    {
        return ['one' => [1], 'two' => [2]];
    }
}
\class_alias('MolliePrefix\\DataProviderTestDoxTest', 'MolliePrefix\\DataProviderTestDoxTest', \false);
