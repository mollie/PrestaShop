<?php

namespace MolliePrefix;

trait AbstractTrait
{
    public abstract function doSomething();
    public function mockableMethod()
    {
        return \true;
    }
    public function anotherMockableMethod()
    {
        return \true;
    }
}
