<?php

class Context
{
    protected static $instance;
    public $cart;
    public $customer;
    public $cookie;
    public $link;
    public $country;
    public $employee;
    public $controller;
    public $override_controller_name_for_translations;
    public $language;
    public $currency;
    public $tab;
    public $shop;
    public $smarty;
    public $mobile_detect;
    public $mode;
    protected $translator = null;
    protected $mobile_device = null;
    protected $is_mobile = null;
    protected $is_tablet = null;
    const DEVICE_COMPUTER = 1;
    const DEVICE_TABLET = 2;
    const DEVICE_MOBILE = 4;
    const MODE_STD = 1;
    const MODE_STD_CONTRIB = 2;
    const MODE_HOST_CONTRIB = 4;
    const MODE_HOST = 8;

    public function getMobileDetect()
    {
        return null;
    }

    public function isMobile()
    {
        return false;
    }

    public function isTablet()
    {
        return false;
    }

    public function getMobileDevice()
    {
        return false;
    }

    public function getDevice()
    {
        return 7;
    }

    protected function checkMobileContext()
    {
        return false;
    }

    public static function getContext()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Context();
        }

        return self::$instance;
    }

    public static function setInstanceForTesting($testInstance)
    {
        self::$instance = $testInstance;
    }

    public static function deleteTestingInstance()
    {
        self::$instance = null;
    }

    public function cloneContext()
    {
        return clone $this;
    }

    public function updateCustomer(Customer $customer)
    {
    }

    public function getTranslator()
    {
    }

    public function getTranslatorFromLocale($locale)
    {
    }

    protected function getTranslationResourcesDirectories()
    {
        return [];
    }
}
