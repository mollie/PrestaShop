<?php

namespace MolliePrefix;

abstract class AbstractMockTestClass implements \MolliePrefix\MockTestInterface
{
    public abstract function doSomething();
    public function returnAnything()
    {
        return 1;
    }
}
\class_alias('MolliePrefix\\AbstractMockTestClass', 'AbstractMockTestClass', \false);
