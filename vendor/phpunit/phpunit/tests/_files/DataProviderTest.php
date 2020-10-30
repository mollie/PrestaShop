<?php

namespace MolliePrefix;

class DataProviderTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerMethod
     */
    public function testAdd($a, $b, $c)
    {
        $this->assertEquals($c, $a + $b);
    }
    public static function providerMethod()
    {
        return [[0, 0, 0], [0, 1, 1], [1, 1, 3], [1, 0, 1]];
    }
}
\class_alias('MolliePrefix\\DataProviderTest', 'MolliePrefix\\DataProviderTest', \false);
