<?php

namespace MolliePrefix;

class ExceptionInAssertPostConditionsTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public $setUp = \false;
    public $assertPreConditions = \false;
    public $assertPostConditions = \false;
    public $tearDown = \false;
    public $testSomething = \false;
    protected function setUp()
    {
        $this->setUp = \true;
    }
    protected function assertPreConditions()
    {
        $this->assertPreConditions = \true;
    }
    public function testSomething()
    {
        $this->testSomething = \true;
    }
    protected function assertPostConditions()
    {
        $this->assertPostConditions = \true;
        throw new \Exception();
    }
    protected function tearDown()
    {
        $this->tearDown = \true;
    }
}
\class_alias('MolliePrefix\\ExceptionInAssertPostConditionsTest', 'ExceptionInAssertPostConditionsTest', \false);
