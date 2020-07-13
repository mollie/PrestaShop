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
use Context;
use Db;
use DbQuery;
use Exception;
use Language;
use Mollie;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tab;

class Installer
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

    public function install()
    {
        foreach (self::getHooks() as $hook) {
            $this->module->registerHook($hook);
        }

        $context = Context::getContext();

        try {
            $this->createMollieStatuses($context->language->id);
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Unable to install Mollie statuses');
            return false;
        }

        try {
            $this->initConfig();
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Unable to install config');
            return false;
        }
        try {
            $this->setDefaultCarrierStatuses();
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Unable to install default carrier statuses');
            return false;
        }

        include(dirname(__FILE__) . '/../../sql/install.php');

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
 
    public static function getHooks()
    {
        return [
            'displayPayment',
            'displayPaymentEU',
            'paymentOptions',
            'displayAdminOrder',
            'displayBackOfficeHeader',
            'displayOrderConfirmation',
            'actionFrontControllerSetMedia',
            'actionEmailSendBefore',
            'actionOrderStatusUpdate',
            'displayPDFInvoice',
            'actionAdminOrdersListingFieldsModifier',
            'actionAdminControllerSetMedia',
        ];
    }

    /**
     * Create new order state for partial refunds.
     *
     * @return boolean
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @since 2.0.0
     *
     */
    private function partialRefundOrderState($languageId)
    {
        $stateExists = false;
        $states = OrderState::getOrderStates((int)$languageId);
        foreach ($states as $state) {
            if ($this->module->lang('Mollie partially refunded') === $state['name']) {
                Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND, (int)$state[OrderState::$definition['primary']]);
                $stateExists = true;
                break;
            }
        }
        if ($stateExists) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#6F8C9F';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->module_name = $this->module->name;
        $orderState->name = [];
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $orderState->name[$language['id_lang']] = $this->module->lang('Mollie partially refunded');
        }
        if ($orderState->add()) {
            $source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$orderState->id . '.gif';
            @copy($source, $destination);
        }
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND, (int)$orderState->id);


        return true;
    }

    public function createMollieStatuses($languageId)
    {
        if (!$this->partialRefundOrderState($languageId)) {
            return false;
        }
        if (!$this->awaitingMollieOrderState($languageId)) {
            return false;
        }

        return true;

    }

    /**
     * @param $languageId
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function awaitingMollieOrderState($languageId)
    {
        $stateExists = false;
        $states = OrderState::getOrderStates((int)$languageId);
        foreach ($states as $state) {
            if ($this->module->lang('Awaiting Mollie payment') === $state['name']) {
                Configuration::updateValue(Mollie\Config\Config::STATUS_MOLLIE_AWAITING, (int)$state[OrderState::$definition['primary']]);
                $stateExists = true;
                break;
            }
        }
        if (!$stateExists) {
            $orderState = new OrderState();
            $orderState->send_email = false;
            $orderState->color = '#4169E1';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->module->name;
            $orderState->name = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $orderState->name[$language['id_lang']] = $this->module->lang('Awaiting Mollie payment');
            }
            if ($orderState->add()) {
                $source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$orderState->id . '.gif';
                @copy($source, $destination);
            }
            Configuration::updateValue(Mollie\Config\Config::STATUS_MOLLIE_AWAITING, (int)$orderState->id);
        }

        return true;
    }

    /**
     * @return void
     *
     */
    protected function initConfig()
    {
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_API_KEY, '');
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_PROFILE_ID, '');
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_SEND_ORDER_CONFIRMATION, false);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE, Mollie\Config\Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_IFRAME, false);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_IMAGES, Mollie\Config\Config::LOGOS_NORMAL);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_ISSUERS, Mollie\Config\Config::ISSUERS_ON_CLICK);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_CSS, '');
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_TRACKING_URLS, '');
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_DEBUG_LOG, Mollie\Config\Config::DEBUG_LOG_ERRORS);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_QRENABLED, false);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES, 0);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, 0);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS, false);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_OPEN, Configuration::get(Mollie\Config\Config::STATUS_MOLLIE_AWAITING));
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_PAID, Configuration::get('PS_OS_PAYMENT'));
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_CANCELED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_EXPIRED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateValue(
            Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND,
            Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND)
        );
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_STATUS_REFUNDED, Configuration::get('PS_OS_REFUND'));
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_MAIL_WHEN_PAID, true);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_MAIL_WHEN_CANCELED, true);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_MAIL_WHEN_EXPIRED, true);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_MAIL_WHEN_REFUNDED, true);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_ACCOUNT_SWITCH, false);
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_CSS, '');

        Configuration::updateValue(Mollie\Config\Config::MOLLIE_API, Mollie\Config\Config::MOLLIE_ORDERS_API);
    }

    /***
     *
     */
    public function setDefaultCarrierStatuses()
    {
        $sql = new DbQuery();
        $sql->select('`' . bqSQL(OrderState::$definition['primary']) . '`');
        $sql->from(bqSQL(OrderState::$definition['table']));
        $sql->where('`shipped` = 1');

        $defaultStatuses = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($defaultStatuses)) {
            return;
        }
        $defaultStatuses = array_map('intval', array_column($defaultStatuses, OrderState::$definition['primary']));
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES, json_encode($defaultStatuses));
    }

    public function installTab($className, $parent, $name, $active = true) {

        $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

        $moduleTab = new Tab();
        $moduleTab->class_name = $className;
        $moduleTab->id_parent = $idParent;
        $moduleTab->module = $this->module->name;
        $moduleTab->active = $active;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $moduleTab->name[$language['id_lang']] = $name;
        }

        if (!$moduleTab->save()) {
            return false;
        }

        return true;
    }
}