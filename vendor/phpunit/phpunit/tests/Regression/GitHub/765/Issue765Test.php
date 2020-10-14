<?php

namespace MolliePrefix;

class Issue765Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testDependee()
    {
        $this->assertTrue(\true);
    }
    /**
     * @depends testDependee
     * @dataProvider dependentProvider
     */
    public function testDependent($a)
    {
        $this->assertTrue(\true);
    }
    public function dependentProvider()
    {
        throw new \Exception();
    }
}
\class_alias('MolliePrefix\\Issue765Test', 'Issue765Test', \false);
