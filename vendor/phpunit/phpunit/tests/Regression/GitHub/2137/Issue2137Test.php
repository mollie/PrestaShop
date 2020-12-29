<?php

namespace MolliePrefix;

class Issue2137Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideBrandService
     * @param $provided
     * @param $expected
     */
    public function testBrandService($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }
    public function provideBrandService()
    {
        return [
            //[true, true]
            new \stdClass(),
        ];
    }
    /**
     * @dataProvider provideBrandService
     * @param $provided
     * @param $expected
     */
    public function testSomethingElseInvalid($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }
}
\class_alias('MolliePrefix\\Issue2137Test', 'Issue2137Test', \false);
