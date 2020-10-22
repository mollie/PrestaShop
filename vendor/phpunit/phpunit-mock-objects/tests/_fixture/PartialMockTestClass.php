<?php

namespace MolliePrefix;

class PartialMockTestClass
{
    public $constructorCalled = \false;
    public function __construct()
    {
        $this->constructorCalled = \true;
    }
    public function doSomething()
    {
    }
    public function doAnotherThing()
    {
    }
}
\class_alias('MolliePrefix\\PartialMockTestClass', 'PartialMockTestClass', \false);
