<?php

abstract class PaymentModule extends Module
{
    public $currentOrder;
    public $currentOrderReference;
    public $currencies = true;
    public $currencies_mode = 'checkbox';

    const DEBUG_MODE = false;

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
