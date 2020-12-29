<?php

namespace MolliePrefix;

use MolliePrefix\PHPUnit\Framework\TestCase;
class Issue2382Test extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOne($test)
    {
        $this->assertInstanceOf(\Exception::class, $test);
    }
    public function dataProvider()
    {
        return [[$this->getMockBuilder(\Exception::class)->getMock()]];
    }
}
\class_alias('MolliePrefix\\Issue2382Test', 'Issue2382Test', \false);
