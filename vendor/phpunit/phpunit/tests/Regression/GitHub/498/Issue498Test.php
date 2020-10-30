<?php

namespace MolliePrefix;

class Issue498Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider shouldBeTrueDataProvider
     * @group falseOnly
     */
    public function shouldBeTrue($testData)
    {
        $this->assertTrue(\true);
    }
    /**
     * @test
     * @dataProvider shouldBeFalseDataProvider
     * @group trueOnly
     */
    public function shouldBeFalse($testData)
    {
        $this->assertFalse(\false);
    }
    public function shouldBeTrueDataProvider()
    {
        //throw new Exception("Can't create the data");
        return [[\true], [\false]];
    }
    public function shouldBeFalseDataProvider()
    {
        throw new \Exception("Can't create the data");
        return [[\true], [\false]];
    }
}
\class_alias('MolliePrefix\\Issue498Test', 'MolliePrefix\\Issue498Test', \false);
