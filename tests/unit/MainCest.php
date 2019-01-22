<?php

class MainCest
{
    public function moduleName(UnitTester $I)
    {
        $module = new Mollie();
        $I->assertEquals('mollie', $module->name);
    }
}
