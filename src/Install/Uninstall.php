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
use OrderState;
use Tab;
use Validate;

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
        $this->deleteMollieStatuses();

        $this->deleteConfig();

        $this->uninstallTabs();

        include(dirname(__FILE__) . '/../../sql/uninstall.php');

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function deleteConfig()
    {
        $configurations = [
            Config::MOLLIE_API_KEY,
            Config::MOLLIE_PROFILE_ID,
            Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            Config::MOLLIE_SEND_ORDER_CONFIRMATION,
            Config::MOLLIE_SEND_NEW_ORDER,
            Config::MOLLIE_IFRAME,
            Config::MOLLIE_IMAGES,
            Config::MOLLIE_ISSUERS,
            Config::MOLLIE_CSS,
            Config::MOLLIE_DEBUG_LOG,
            Config::MOLLIE_QRENABLED,
            Config::MOLLIE_DISPLAY_ERRORS,
            Config::MOLLIE_STATUS_OPEN,
            Config::MOLLIE_STATUS_PAID,
            Config::MOLLIE_STATUS_CANCELED,
            Config::MOLLIE_STATUS_EXPIRED,
            Config::MOLLIE_STATUS_PARTIAL_REFUND,
            Config::MOLLIE_STATUS_REFUNDED,
            Config::MOLLIE_STATUS_SHIPPING,
            Config::MOLLIE_MAIL_WHEN_SHIPPING,
            Config::MOLLIE_MAIL_WHEN_OPEN,
            Config::MOLLIE_MAIL_WHEN_PAID,
            Config::MOLLIE_MAIL_WHEN_CANCELED,
            Config::MOLLIE_MAIL_WHEN_EXPIRED,
            Config::MOLLIE_MAIL_WHEN_REFUNDED,
            Config::MOLLIE_ACCOUNT_SWITCH,
            Config::MOLLIE_METHOD_COUNTRIES,
            Config::MOLLIE_METHOD_COUNTRIES_DISPLAY,
            Config::MOLLIE_API,
            Config::MOLLIE_AUTO_SHIP_STATUSES,
            Config::MOLLIE_TRACKING_URLS,
            Config::MOLLIE_METHODS_LAST_CHECK,
            Config::METHODS_CONFIG,
            Config::MOLLIE_STATUS_PARTIALLY_SHIPPED,
            Config::MOLLIE_STATUS_COMPLETED,
            Config::MOLLIE_STATUS_ORDER_COMPLETED,
            Config::MOLLIE_MAIL_WHEN_COMPLETED,
            Config::STATUS_MOLLIE_AWAITING,
        ];

        $this->deleteConfigurations($configurations);
    }

    /**
     * @param array $configurations
     */
    private function deleteConfigurations(array $configurations)
    {
        foreach ($configurations as $configuration) {
            Configuration::deleteByName($configuration);
        }
    }

    private function deleteMollieStatuses()
    {
        foreach (Config::getMollieOrderStatuses() as $mollieStatus) {
            $statusId = Configuration::get($mollieStatus);
            $orderState = new OrderState($statusId);
            if (!Validate::isLoadedObject($orderState)) {
                return;
            }
            $orderState->deleted = 1;
            $orderState->update();
        }
    }

    private function uninstallTabs()
    {
        $tabs = [
            'AdminMollieAjax',
            'AdminMollieModule'
        ];

        foreach ($tabs as $tab) {
            $idTab = Tab::getIdFromClassName($tab);

            if (!$idTab) {
                continue;
            }

            $tab = new Tab($idTab);
            if (!$tab->delete()) {
                return false;
            }
        }
    }
}
