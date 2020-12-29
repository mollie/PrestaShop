<?php

namespace MolliePrefix;

class DataProviderDependencyTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $this->markTestSkipped('This test should be skipped.');
        $this->assertTrue(\true);
    }
    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/1896
     * @depends testReference
     * @dataProvider provider
     */
    public function testDependency($param)
    {
    }
    public function provider()
    {
        $this->markTestSkipped('Any test with this data provider should be skipped.');
        return [];
    }
}
\class_alias('MolliePrefix\\DataProviderDependencyTest', 'DataProviderDependencyTest', \false);
