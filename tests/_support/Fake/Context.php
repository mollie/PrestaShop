<?php
/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

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
