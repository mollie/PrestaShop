<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
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

namespace Mollie\Install;

use Configuration;
use Mollie;
use Mollie\Config\Config;

class Uninstall
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function uninstall()
    {
        $this->deleteConfig();

        include(dirname(__FILE__) . '/../../sql/uninstall.php');

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function deleteConfig()
    {
        Configuration::deleteByName(Config::MOLLIE_API_KEY);
        Configuration::deleteByName(Config::MOLLIE_PROFILE_ID);
        Configuration::deleteByName(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        Configuration::deleteByName(Config::MOLLIE_SEND_ORDER_CONFIRMATION);
        Configuration::deleteByName(Config::MOLLIE_IFRAME);
        Configuration::deleteByName(Config::MOLLIE_IMAGES);
        Configuration::deleteByName(Config::MOLLIE_ISSUERS);
        Configuration::deleteByName(Config::MOLLIE_CSS);
        Configuration::deleteByName(Config::MOLLIE_DEBUG_LOG);
        Configuration::deleteByName(Config::MOLLIE_QRENABLED);
        Configuration::deleteByName(Config::MOLLIE_DISPLAY_ERRORS);
        Configuration::deleteByName(Config::MOLLIE_STATUS_OPEN);
        Configuration::deleteByName(Config::MOLLIE_STATUS_PAID);
        Configuration::deleteByName(Config::MOLLIE_STATUS_CANCELED);
        Configuration::deleteByName(Config::MOLLIE_STATUS_EXPIRED);
        Configuration::deleteByName(Config::MOLLIE_STATUS_PARTIAL_REFUND);
        Configuration::deleteByName(Config::MOLLIE_STATUS_REFUNDED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_OPEN);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_PAID);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_CANCELED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_EXPIRED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_REFUNDED);
        Configuration::deleteByName(Config::MOLLIE_ACCOUNT_SWITCH);
        Configuration::deleteByName(Config::MOLLIE_METHOD_COUNTRIES);
        Configuration::deleteByName(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
        Configuration::deleteByName(Config::MOLLIE_API);
        Configuration::deleteByName(Config::MOLLIE_AUTO_SHIP_STATUSES);
        Configuration::deleteByName(Config::MOLLIE_TRACKING_URLS);
        Configuration::deleteByName(Config::MOLLIE_METHODS_LAST_CHECK);
        Configuration::deleteByName(Config::METHODS_CONFIG);
    }
}