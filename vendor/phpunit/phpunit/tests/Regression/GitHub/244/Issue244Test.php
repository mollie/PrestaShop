<?php

namespace MolliePrefix;

class Issue244Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode 123StringCode
     */
    public function testWorks()
    {
        throw new \MolliePrefix\Issue244Exception();
    }
    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode OtherString
     */
    public function testFails()
    {
        throw new \MolliePrefix\Issue244Exception();
    }
    /**
     * @expectedException Issue244Exception
     * @expectedExceptionCode 123
     */
    public function testFailsTooIfExpectationIsANumber()
    {
        throw new \MolliePrefix\Issue244Exception();
    }
    /**
     * @expectedException Issue244ExceptionIntCode
     * @expectedExceptionCode 123String
     */
    public function testFailsTooIfExceptionCodeIsANumber()
    {
        throw new \MolliePrefix\Issue244ExceptionIntCode();
    }
}
\class_alias('MolliePrefix\\Issue244Test', 'MolliePrefix\\Issue244Test', \false);
class Issue244Exception extends \Exception
{
    public function __construct()
    {
        $this->code = '123StringCode';
    }
}
\class_alias('MolliePrefix\\Issue244Exception', 'MolliePrefix\\Issue244Exception', \false);
class Issue244ExceptionIntCode extends \Exception
{
    public function __construct()
    {
        $this->code = 123;
    }
}
\class_alias('MolliePrefix\\Issue244ExceptionIntCode', 'MolliePrefix\\Issue244ExceptionIntCode', \false);
