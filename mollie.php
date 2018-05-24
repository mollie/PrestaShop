<?php
/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
 */

require_once(dirname(__FILE__).'/lib/vendor/autoload.php');
if (!function_exists('\\Hough\\Psr7\\str')) {
    require_once __DIR__.'/lib/vendor/ehough/psr7/src/functions.php';
}

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class Mollie
 */
class Mollie extends PaymentModule
{
    /** @var \Mollie\Api\MollieApiClient|null */
    public $api = null;
    /** @var array $statuses */
    public $statuses = array();
    /** @var array $lang */
    public $lang = array();
    /**
    * Currency restrictions per payment method
    *
    * @var array
    */
    public static $methodCurrencies = array(
        'banktransfer'  => array('eur'),
        'belfius'       => array('eur'),
        'bitcoin'       => array('eur'),
        'creditcard'    => array('aud', 'bgn', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'hrk', 'huf', 'ils', 'isk', 'jpy', 'pln', 'ron', 'sek', 'usd'),
        'directdebit'   => array('eur'),
        'giftcard'      => array('eur'),
        'ideal'         => array('eur'),
        'inghomepay'    => array('eur'),
        'kbc'           => array('eur'),
        'bancontact'    => array('eur'),
        'paypal'        => array('aud', 'brl', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'huf', 'ils', 'jpy', 'mxn', 'myr', 'nok', 'nzd', 'php', 'pln', 'rub', 'sek', 'sgd', 'thb', 'twd', 'usd'),
        'paysafecard'   => array('eur'),
        'sofort'        => array('eur'),
    );

    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRASH = 4;

    const NAME = 'mollie';

    const PAYMENTSCREEN_LOCALE_BROWSER_LOCALE = 'browser_locale';
    const PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE = 'website_locale';

    const LOGOS_BIG = 'big';
    const LOGOS_NORMAL = 'normal';
    const LOGOS_HIDE = 'hide';

    const ISSUERS_ON_CLICK = 'on-click';
    const ISSUERS_OWN_PAGE = 'own-page';
    const ISSUERS_PAYMENT_PAGE = 'payment-page';
    const METHODS_CONFIG = 'MOLLIE_METHODS_CONFIG';

    const DEBUG_LOG_NONE = 0;
    const DEBUG_LOG_ERRORS = 1;
    const DEBUG_LOG_ALL = 2;

    const MOLLIE_API_KEY = 'MOLLIE_API_KEY';
    const MOLLIE_DESCRIPTION = 'MOLLIE_DESCRIPTION';
    const MOLLIE_PAYMENTSCREEN_LOCALE = 'MOLLIE_PAYMENTSCREEN_LOCALE';
    const MOLLIE_IMAGES = 'MOLLIE_IMAGES';
    const MOLLIE_ISSUERS = 'MOLLIE_ISSUERS';
    const MOLLIE_CSS = 'MOLLIE_CSS';
    const MOLLIE_DEBUG_LOG = 'MOLLIE_DEBUG_LOG';
    const MOLLIE_QRENABLED = 'MOLLIE_QRENABLED';
    const MOLLIE_DISPLAY_ERRORS = 'MOLLIE_DISPLAY_ERRORS';
    const MOLLIE_USE_PROFILE_WEBHOOK = 'MOLLIE_USE_PROFILE_WEBHOOK';
    const MOLLIE_PENDING = 'MOLLIE_PENDING';
    const MOLLIE_STATUS_OPEN = 'MOLLIE_STATUS_OPEN';
    const MOLLIE_STATUS_PAID = 'MOLLIE_STATUS_PAID';
    const MOLLIE_STATUS_CANCELLED = 'MOLLIE_STATUS_CANCELLED';
    const MOLLIE_STATUS_EXPIRED = 'MOLLIE_STATUS_EXPIRED';
    const MOLLIE_STATUS_PARTIAL_REFUND = 'MOLLIE_PARTIAL_REFUND';
    const MOLLIE_STATUS_REFUNDED = 'MOLLIE_STATUS_REFUNDED';
    const MOLLIE_MAIL_WHEN_OPEN = 'MOLLIE_MAIL_WHEN_OPEN';
    const MOLLIE_MAIL_WHEN_PAID = 'MOLLIE_MAIL_WHEN_PAID';
    const MOLLIE_MAIL_WHEN_CANCELLED = 'MOLLIE_MAIL_WHEN_CANCELLED';
    const MOLLIE_MAIL_WHEN_EXPIRED = 'MOLLIE_MAIL_WHEN_EXPIRED';
    const MOLLIE_MAIL_WHEN_REFUNDED = 'MOLLIE_MAIL_WHEN_REFUNDED';
    const PARTIAL_REFUND_CODE = 'partial_refund';

    /**
     * Hooks for this module
     *
     * @var array $hooks
     */
    public $hooks = array(
        'displayPayment',
        'displayPaymentEU',
        'paymentOptions',
        'displayAdminOrder',
        'displayHeader',
        'displayBackOfficeHeader',
        'displayOrderConfirmation',
    );

    /** @var array $methods */
    public static $methods = array(
        'banktransfer' => 'Bank',
        'belfius '     => 'Belfius',
        'bitcoin '     => 'Bitcoin',
        'creditcard'   => 'Credit Card',
        'directdebit'  => 'Direct Debit',
        'giftcard'     => 'Giftcard',
        'ideal'        => 'iDEAL',
        'inghomepay '  => 'ING Homepay',
        'kbc'          => 'KBC',
        'bancontact'   => 'Bancontact',
        'paypal'       => 'PayPal',
        'paysafecard'  => 'Paysafecard',
        'sofort'       => 'Sofort Banking',
    );

    /**
     * Mollie constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'mollie';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.0';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mollie Payment Module');
        $this->description = $this->l('Mollie Payments');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Mollie Payment Module?');

        $this->controllers = array('payment', 'return', 'webhook', 'qrcode');

        try {
            $this->api = new \Mollie\Api\MollieApiClient();
            if (Configuration::get(static::MOLLIE_API_KEY)) {
                $this->api->setApiKey(Configuration::get(static::MOLLIE_API_KEY));
            }
            if (defined('_TB_VERSION_')) {
                $this->api->addVersionString('ThirtyBees/'._TB_VERSION_);
                $this->api->addVersionString("MollieThirtyBees/{$this->version}");
            } else {
                $this->api->addVersionString('PrestaShop/'._PS_VERSION_);
                $this->api->addVersionString("MolliePrestaShop/{$this->version}");
            }
        } catch (\Mollie\Api\Exceptions\IncompatiblePlatform $e) {
            Logger::addLog(__METHOD__.' - System incompatible: '.$e->getMessage(), static::CRASH);
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->warning = $this->l('Payment error:').$e->getMessage();
            Logger::addLog(__METHOD__.' said: '.$this->warning, static::CRASH);
        }

        $this->statuses = array(
            \Mollie\Api\Types\PaymentStatus::STATUS_PAID     => Configuration::get(static::MOLLIE_STATUS_PAID),
            \Mollie\Api\Types\PaymentStatus::STATUS_CANCELED => Configuration::get(static::MOLLIE_STATUS_CANCELLED),
            \Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED  => Configuration::get(static::MOLLIE_STATUS_EXPIRED),
            static::PARTIAL_REFUND_CODE                      => Configuration::get(static::MOLLIE_STATUS_PARTIAL_REFUND),
            \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED  => Configuration::get(static::MOLLIE_STATUS_REFUNDED),
            \Mollie\Api\Types\PaymentStatus::STATUS_OPEN     => Configuration::get(static::MOLLIE_STATUS_OPEN),
        );

        // Load all translatable text here so we have a single translation point
        $this->lang = array(
            \Mollie\Api\Types\PaymentStatus::STATUS_PAID                                                                                           => $this->l('paid'),
            \Mollie\Api\Types\PaymentStatus::STATUS_CANCELED                                                                                       => $this->l('cancelled'),
            \Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED                                                                                        => $this->l('expired'),
            \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED                                                                                        => $this->l('refunded'),
            \Mollie\Api\Types\PaymentStatus::STATUS_OPEN                                                                                           => $this->l('bankwire pending'),
            static::PARTIAL_REFUND_CODE                                                                                                            => $this->l('partially refunded'),
            'This payment method is not available.'                                                                                                => $this->l('This payment method is not available.'),
            'Click here to continue'                                                                                                               => $this->l('Click here to continue'),
            'This payment method is only available for Euros.'                                                                                     => $this->l('This payment method is only available for Euros.'),
            'There was an error while processing your request: '                                                                                   => $this->l('There was an error while processing your request: '),
            'The order with this id does not exist.'                                                                                               => $this->l('The order with this id does not exist.'),
            'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.'      => $this->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.'),
            'Unfortunately your payment was expired.'                                                                                              => $this->l('Unfortunately your payment was expired.'),
            'Thank you. Your payment has been received.'                                                                                           => $this->l('Thank you. Your payment has been received.'),
            'The transaction has an unexpected status.'                                                                                            => $this->l('The transaction has an unexpected status.'),
            'You are not authorised to see this page.'                                                                                             => $this->l('You are not authorised to see this page.'),
            'Continue shopping'                                                                                                                    => $this->l('Continue shopping'),
            'Welcome back'                                                                                                                         => $this->l('Welcome back'),
            'Select your bank:'                                                                                                                    => $this->l('Select your bank:'),
            'OK'                                                                                                                                   => $this->l('OK'),
            'Different payment method'                                                                                                             => $this->l('Different payment method'),
            'Pay with %s'                                                                                                                          => $this->l('Pay with %s'),
            'Refund this order'                                                                                                                    => $this->l('Refund this order'),
            'Mollie refund'                                                                                                                        => $this->l('Mollie refund'),
            'Refund order #%d through the Mollie API.'                                                                                             => $this->l('Refund order #%d through the Mollie API.'),
            'The order has been refunded!'                                                                                                         => $this->l('The order has been refunded!'),
            'Mollie B.V. will transfer the money back to the customer on the next business day.'                                                   => $this->l('Mollie B.V. will transfer the money back to the customer on the next business day.'),
            'Awaiting Mollie payment'                                                                                                              => $this->l('Awaiting Mollie payment'),
            'Mollie partially refunded'                                                                                                            => $this->l('Mollie partially refunded'),
            'iDEAL'                                                                                                                                => $this->l('iDEAL'),
            'Credit card'                                                                                                                          => $this->l('Credit card'),
            'Bancontact'                                                                                                                           => $this->l('Bancontact'),
            'SOFORT Banking'                                                                                                                       => $this->l('SOFORT Banking'),
            'SEPA Direct Debit'                                                                                                                    => $this->l('SEPA Direct Debit'),
            'Belfius Pay Button'                                                                                                                   => $this->l('Belfius Pay Button'),
            'Bitcoin'                                                                                                                              => $this->l('Bitcoin'),
            'PODIUM Cadeaukaart'                                                                                                                   => $this->l('PODIUM Cadeaukaart'),
            'Gift cards'                                                                                                                           => $this->l('Gift cards'),
            'Bank transfer'                                                                                                                        => $this->l('Bank transfer'),
            'PayPal'                                                                                                                               => $this->l('PayPal'),
            'paysafecard'                                                                                                                          => $this->l('paysafecard'),
            'KBC/CBC Payment Button'                                                                                                               => $this->l('KBC/CBC Payment Button'),
        );
    }

    /**
     * Installs the Mollie Payments Module
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function install()
    {
        if (!parent::install()) {
            $this->_errors[] = 'Unable to install module';

            return false;
        }

        foreach ($this->hooks as $hook) {
            $this->registerHook($hook);
        }

        if (!$this->pendingOrderState()) {
            $this->_errors[] = 'Unable to install Mollie pending order state';

            return false;
        }

        if (!$this->partialRefundOrderState()) {
            $this->_errors[] = 'Unable to install Mollie partially refunded order state';

            return false;
        }

        $this->initConfig();

        include(dirname(__FILE__).'/sql/install.php');

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
        }

        Configuration::deleteByName(static::MOLLIE_API_KEY);
        Configuration::deleteByName(static::MOLLIE_DESCRIPTION);
        Configuration::deleteByName(static::MOLLIE_PAYMENTSCREEN_LOCALE);
        Configuration::deleteByName(static::MOLLIE_ISSUERS);
        Configuration::deleteByName(static::MOLLIE_CSS);
        Configuration::deleteByName(static::MOLLIE_DEBUG_LOG);
        Configuration::deleteByName(static::MOLLIE_QRENABLED);
        Configuration::deleteByName(static::MOLLIE_DISPLAY_ERRORS);
        Configuration::deleteByName(static::MOLLIE_USE_PROFILE_WEBHOOK);
        Configuration::deleteByName(static::MOLLIE_PENDING);
        Configuration::deleteByName(static::MOLLIE_STATUS_OPEN);
        Configuration::deleteByName(static::MOLLIE_STATUS_PAID);
        Configuration::deleteByName(static::MOLLIE_STATUS_CANCELLED);
        Configuration::deleteByName(static::MOLLIE_STATUS_EXPIRED);
        Configuration::deleteByName(static::MOLLIE_STATUS_PARTIAL_REFUND);
        Configuration::deleteByName(static::MOLLIE_STATUS_REFUNDED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_OPEN);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_PAID);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_CANCELLED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_EXPIRED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_REFUNDED);

        return parent::uninstall();
    }


    /**
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function reinstall()
    {
        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
            $this->registerHook($hook);
        }

        $this->initConfig();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    protected function initConfig()
    {
        Configuration::updateGlobalValue(static::MOLLIE_API_KEY, '');
        Configuration::updateGlobalValue(static::MOLLIE_DESCRIPTION, 'Cart %');
        Configuration::updateGlobalValue(static::MOLLIE_PAYMENTSCREEN_LOCALE, static::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE);
        Configuration::updateGlobalValue(static::MOLLIE_ISSUERS, static::ISSUERS_ON_CLICK);
        Configuration::updateGlobalValue(static::MOLLIE_CSS, '');
        Configuration::updateGlobalValue(static::MOLLIE_DEBUG_LOG, static::DEBUG_LOG_ERRORS);
        Configuration::updateGlobalValue(static::MOLLIE_QRENABLED, false);
        Configuration::updateGlobalValue(static::MOLLIE_DISPLAY_ERRORS, false);
        Configuration::updateGlobalValue(static::MOLLIE_USE_PROFILE_WEBHOOK, false);
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_OPEN, Configuration::get(static::MOLLIE_PENDING));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_PAID, Configuration::get('PS_OS_PAYMENT'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_CANCELLED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_EXPIRED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_PARTIAL_REFUND, Configuration::get(static::MOLLIE_STATUS_PARTIAL_REFUND));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_REFUNDED, Configuration::get('PS_OS_REFUND'));
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_PAID, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_CANCELLED, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_EXPIRED, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_REFUNDED, true);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }


    /**
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $cookie = Context::getContext()->cookie;
        $lang = isset($cookie->id_lang) ? (int) $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT');
        $lang = $lang == 0 ? Configuration::get('PS_LANG_DEFAULT') : $lang;
        $this->context->smarty->assign(array(
            'link'       => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__.'modules/'.basename(__FILE__, '.php').'/',
        ));

        $updateMessage = defined('_TB_VERSION_')
            ? $this->getUpdateMessage('https://github.com/mollie/thirtybees')
            : $this->getUpdateMessage('https://github.com/mollie/PrestaShop');
        if ($updateMessage === 'updateAvailable') {
            $updateMessage = $this->display(__FILE__, 'views/templates/admin/download_update.tpl');
        }
        $resultMessage = '';
        $warningMessage = '';

        $payscreenLocaleOptions = array(
            static::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE      => $this->l('Do not send locale, use browser language'),
            static::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE => $this->l('Send webshop locale'),
        );

        $imageOptions = array(
            static::LOGOS_BIG    => $this->l('big'),
            static::LOGOS_NORMAL => $this->l('normal'),
            static::LOGOS_HIDE   => $this->l('hide'),
        );
        $issuerOptions = array(
            static::ISSUERS_ON_CLICK       => $this->l('On click'),
            static::ISSUERS_OWN_PAGE       => $this->l('Own page'),
            static::ISSUERS_PAYMENT_PAGE   => $this->l('Payment page'),
        );
        $loggerOptions = array(
            static::DEBUG_LOG_NONE   => $this->l('Nothing'),
            static::DEBUG_LOG_ERRORS => $this->l('Errors'),
            static::DEBUG_LOG_ALL    => $this->l('Everything'),
        );

        if (Tools::isSubmit('Mollie_Config_Save')) {
            $resultMessage = $this->getSaveResult(
                array_keys($payscreenLocaleOptions), array_keys($imageOptions),
                array_keys($issuerOptions), array_keys($loggerOptions)
            );
        }

        $data = array(
            'form_action'              => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
            'config_title'             => $this->l('Mollie Configuration'),
            'config_legend'            => $this->l('Mollie Settings'),
            'update_message'           => $updateMessage,
            'all_statuses'             => array_merge(array(array('id_order_state' => 0, 'name' => $this->l('Skip this status'), 'color' => '#565656')), OrderState::getOrderStates($lang)),
            'payscreen_locale_options' => $payscreenLocaleOptions,
            'image_options'            => $imageOptions,
            'issuer_options'           => $issuerOptions,
            'logger_options'           => $loggerOptions,
            'title_status'             => $this->l('%s statuses:'),
            'title_visual'             => $this->l('Visual settings:'),
            'title_debug'              => $this->l('Debug info:'),
            'msg_result'               => $resultMessage,
            'msg_warning'              => $warningMessage,
            'path'                     => $this->_path,
            'val_api_key'              => Configuration::get(static::MOLLIE_API_KEY),
            'val_desc'                 => Configuration::get(static::MOLLIE_DESCRIPTION),
            'payscreen_locale_value'   => Configuration::get(static::MOLLIE_PAYMENTSCREEN_LOCALE),
            'val_issuers'              => Configuration::get(static::MOLLIE_ISSUERS),
            'val_css'                  => Configuration::get(static::MOLLIE_CSS),
            'val_errors'               => Configuration::get(static::MOLLIE_DISPLAY_ERRORS),
            'val_qrenabled'            => Configuration::get(Mollie::MOLLIE_QRENABLED),
            'val_logger'               => Configuration::get(static::MOLLIE_DEBUG_LOG),
            'val_save'                 => $this->l('Save'),
            'lang'                     => $this->lang,
            'methods'                  => $this->getMethodsForConfig(),
        );

        $messageStatus = $this->l('Status for %s payments');
        $descriptionStatus = $this->l('`%s` payments get status `%s`');
        $messageMail = $this->l('Send mails when %s');
        $descriptionMail = $this->l('Send mails when transaction status becomes %s?');
        foreach ($this->statuses as $name => $val) {
            $val = (int) $val;
            $data['msg_status_'.$name] = sprintf($messageStatus, $this->lang[$name]);
            if ($val) {
                $data['desc_status_'.$name] = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->lang[$name],
                        Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                            'SELECT `name`
                            FROM `'._DB_PREFIX_.'order_state_lang`
                            WHERE `id_order_state` = '.(int) $val.'
                            AND `id_lang` = '.(int) $lang
                        )
                    )
                );
            } else {
                $data['desc_status_'.$name] = sprintf($this->l('`%s` payments do not get a status'), $this->lang[$name]);
            }
            $data['val_status_'.$name] = $val;
            $data['msg_mail_'.$name] = sprintf($messageMail, $this->lang[$name]);
            $data['desc_mail_'.$name] = sprintf($descriptionMail, $this->lang[$name]);
            $data['val_mail_'.$name] = Configuration::get('MOLLIE_MAIL_WHEN_'.Tools::strtoupper($name));
            $data['statuses'][] = $name;
        }

        $this->context->controller->addJS($this->_path.'views/js/sweetalert-2.1.0.min.js');
        $this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/jquery.sortable.js');
        $this->context->smarty->assign($data);

        return $this->display(__FILE__, 'views/templates/admin/mollie_config.tpl');
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function lang($str)
    {
        if (array_key_exists($str, $this->lang)) {
            return $this->lang[$str];
        }

        return $str;
    }

    /**
     * @param int $orderId
     * @param int $status
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setOrderStatus($orderId, $status)
    {
        if (empty($this->statuses[$status])) {
            return;
        }
        $statusId = (int) $this->statuses[$status];
        $order = new Order((int) $orderId);
        if ($statusId === (int) $order->current_state) {
            return;
        }

        $history = new OrderHistory();
        $history->id_order = $orderId;
        $history->changeIdOrderState($statusId, $orderId, !$order->hasInvoice());

        if (Configuration::get('MOLLIE_MAIL_WHEN_'.Tools::strtoupper($status))) {
            $history->addWithemail();
        } else {
            $history->add();
        }
    }

    /**
     * @param string $column
     * @param int    $id
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */

    public function getPaymentBy($column, $id)
    {
        $paidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            sprintf(
                'SELECT * FROM `%s` WHERE `%s` = \'%s\' AND `bank_status` = \'%s\'',
                _DB_PREFIX_.'mollie_payments',
                bqSQL($column),
                pSQL($id),
                \Mollie\Api\Types\PaymentStatus::STATUS_PAID
            )
        );

        if ($paidPayment) {
            return $paidPayment;
        }

        $nonPaidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            sprintf(
                'SELECT * FROM `%s` WHERE `%s` = \'%s\' ORDER BY `created_at` DESC',
                _DB_PREFIX_.'mollie_payments',
                bqSQL($column),
                pSQL($id)
            )
        );

        return $nonPaidPayment;
    }

    /**
     * @param array $payscreenLocaleOptions
     * @param array $imageOptions
     * @param array $issuerOptions
     * @param array $loggerOptions
     *
     * @return string
     * @throws PrestaShopException
     */
    protected function getSaveResult(
        array $payscreenLocaleOptions = array(),
        array $imageOptions = array(),
        array $issuerOptions = array(),
        array $loggerOptions = array()
    )
    {
        $errors = array();

        $mollieApiKey = Tools::getValue('Mollie_Api_Key');

        if (!empty($mollieApiKey) && strpos($mollieApiKey, 'live') !== 0 && strpos($mollieApiKey, 'test') !== 0) {
            $errors[] = $this->l('The API key needs to start with test or live.');
        }

        $mollieDescription = Tools::getValue('Mollie_Description');

        if (Tools::getValue('Mollie_Payment_Methods') && @json_decode(Tools::getValue('Mollie_Payment_Methods'))) {
            Configuration::updateValue(static::METHODS_CONFIG, json_encode(@json_decode(Tools::getValue('Mollie_Payment_Methods'))));
        }

        $molliePaymentscreenLocale = Tools::getValue('Mollie_Paymentscreen_Locale');

        if (!in_array($molliePaymentscreenLocale, $payscreenLocaleOptions)) {
            $errors[] = $this->l('Invalid locale setting.');
        }

        $mollieImages = Tools::getValue('Mollie_Images');

        if (!in_array($mollieImages, $imageOptions)) {
            $errors[] = $this->l('Invalid image setting.');
        }

        $mollieIssuers = Tools::getValue('Mollie_Issuers');

        if (!in_array($mollieIssuers, $issuerOptions)) {
            $errors[] = $this->l('Invalid issuer setting.');
        }

        $mollieCss = Tools::getValue('Mollie_Css');

        if (!isset($mollieCss)) {
            $mollieCss = '';
        }

        $mollieLogger = Tools::getValue('Mollie_Logger');

        if (!in_array($mollieLogger, $loggerOptions)) {
            $errors[] = $this->l('Invalid debug log setting.');
        }

        $mollieQrEnabled = (bool) Tools::getValue('Mollie_Qrenabled');

        $mollieErrors = Tools::getValue('Mollie_Errors');

        if (!isset($mollieErrors)) {
            $mollieErrors = false;
        } else {
            $mollieErrors = ($mollieErrors == 1);
        }

        foreach ($this->statuses as $name => $val) {
            if (!is_numeric(Tools::getValue('Mollie_Status_'.$name))) {
                $errors[] = Tools::ucfirst($name).'('.Tools::ucfirst($val).') status must be numeric.';
            }
        }

        if (empty($errors)) {
            Configuration::updateValue(static::MOLLIE_API_KEY, $mollieApiKey);
            Configuration::updateValue(static::MOLLIE_DESCRIPTION, $mollieDescription);
            Configuration::updateValue(static::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            Configuration::updateValue(static::MOLLIE_ISSUERS, $mollieIssuers);
            Configuration::updateValue(static::MOLLIE_QRENABLED, (bool) $mollieQrEnabled);
            Configuration::updateValue(static::MOLLIE_CSS, $mollieCss);
            Configuration::updateValue(static::MOLLIE_DISPLAY_ERRORS, (int) $mollieErrors);
            Configuration::updateValue(static::MOLLIE_DEBUG_LOG, (int) $mollieLogger);

            foreach (array_keys($this->statuses) as $name) {
                $new = (int) Tools::getValue('Mollie_Status_'.$name);
                $this->statuses[$name] = $new;
                Configuration::updateValue('MOLLIE_STATUS_'.Tools::strtoupper($name), $new);

                if ($name != \Mollie\Api\Types\PaymentStatus::STATUS_OPEN) {
                    Configuration::updateValue(
                        'MOLLIE_MAIL_WHEN_'.Tools::strtoupper($name),
                        Tools::getValue('Mollie_Mail_When_'.$name) ? true : false
                    );
                }
            }
            $resultMessage = $this->l('The configuration has been saved!');
        } else {
            $resultMessage = 'The configuration could not be saved:<br /> - '.implode('<br /> - ', $errors);
        }
        return $resultMessage;
    }

    /**
     * @param string|null $url
     *
     * @return bool|null|string|string[]
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ErrorException
     */
    protected function getLatestVersion($url = null)
    {
        if (!$url) {
            $url = (defined('_TB_VERSION_')
                ? 'https://api.github.com/repos/mollie/thirtybees/releases/latest'
                : 'https://api.github.com/repos/mollie/PrestaShop/releases/latest');
        }
        $curl = new \Curl\Curl();
        $response = $curl->get($url);
        if (!is_object($response)) {
            throw new PrestaShopException($this->l('Warning: Could not retrieve update file from github.'));
        }
        if (empty($response->assets[0]->browser_download_url)) {
            throw new PrestaShopException($this->l('No download package found for the latest release.'));
        }

        return array(
            'version'  => $response->tag_name,
            'download' => $response->assets[0]->browser_download_url,
        );
    }

    /**
     * @param string $url
     *
     * @return string|true
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function getUpdateMessage($url)
    {
        $updateMessage = '';
        $updateXml = $this->getUpdateXML($url);
        if ($updateXml === false) {
            $updateMessage = $this->l('Warning: Could not retrieve update xml file from github.');
        } else {
            try {
                /** @var SimpleXMLElement $tags */
                @$tags = new SimpleXMLElement($updateXml);
                if (!empty($tags) && isset($tags->entry, $tags->entry[0], $tags->entry[0]->id)) {
                    $title = $tags->entry[0]->id;
                    $latestVersion = preg_replace("/[^0-9,.]/", '', Tools::substr($title, strrpos($title, '/')));
                    if (!version_compare($this->version, $latestVersion, '>=')) {
                        $this->context->smarty->assign(array(
                            'this_version'    => $this->version,
                            'release_version' => $latestVersion,
                        ));
                        $updateMessage = 'updateAvailable';
                    }
                } else {
                    $updateMessage = $this->l('Warning: Update xml file from github follows an unexpected format.');
                }
            } catch (Exception $e) {
                $updateMessage = $this->l('Warning: Update xml file from github follows an unexpected format.');
            }
        }

        return $updateMessage;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getUpdateXML($url)
    {
        return @Tools::file_get_contents($url.'/releases.atom');
    }


    /**
     * @param int    $orderId
     * @param string $transactionId
     *
     * @return array
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function doRefund($orderId, $transactionId)
    {
        try {
            $payment = $this->api->payments->get($transactionId);
            if ((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value > 0) {
                $payment->refund(array(
                    'amount' => array(
                        'currency' => (string) $payment->amount->currency,
                        'value'    => (string) number_format(((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value), 2),
                    ),
                ));
            }
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'status'      => 'fail',
                'msg_fail'    => $this->lang('The order could not be refunded!'),
                'msg_details' => $this->lang('Reason:').' '.$e->getMessage(),
            );
        }

        // Tell status to shop
        $this->setOrderStatus($orderId, \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED);

        // Save status in mollie_payments table
        $updateData = array(
            'updated_at'  => date('Y-m-d H:i:s'),
            'bank_status' => \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED,
        );

        Db::getInstance(_PS_USE_SQL_SLAVE_)->update('mollie_payments', $updateData, '`order_id` = '.(int) $orderId);

        return array(
            'status'      => 'success',
            'msg_success' => $this->lang('The order has been refunded!'),
            'msg_details' => $this->lang(
                'Mollie B.V. will transfer the money back to the customer on the next business day.'
            ),
        );
    }

    /**
     * @return array
     *
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PrestaShopException
     */
    public function getIssuerList()
    {
        $methods = array();
        foreach ($this->api->methods->all(array('include' => 'issuers')) as $method) {
            /** @var \Mollie\Api\Resources\Method $method */
            foreach ((array) $method->issuers as $issuer) {
                if (!$issuer) {
                    continue;
                }

                $issuer->href = $this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    array('method' => $method->id, 'issuer' => $issuer->id),
                    true
                );

                if (!isset($methods[$method->id])) {
                    $methods[$method->id] = array();
                }
                $methods[$method->id][$issuer->id] = $issuer;
            }
        }

        return $methods;
    }

    /**
     * @param string|null $file
     *
     * @throws PrestaShopException
     */
    protected function addCSSFile($file = null)
    {
        if (is_null($file)) {
            $file = Configuration::get(static::MOLLIE_CSS);
        }
        if (empty($file)) {
            if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
                // Use a modified css file to display the new 1.6 default layout
                $file = $this->_path.'views/css/mollie_15.css';
            } else {
                // Use default css file
                $file = $this->_path.'views/css/mollie.css';
            }
        } else {
            // Use a custom css file
            if (defined('_PS_BASE_URL_')) {
                $file = str_replace('{BASE}', _PS_BASE_URL_, $file);
            }
            if (defined('_PS_THEME_DIR_')) {
                $file = str_replace('{THEME}', _PS_THEME_DIR_, $file);
            }
            if (defined('_PS_CSS_DIR_')) {
                $file = str_replace('{CSS}', _PS_CSS_DIR_, $file);
            }
            if (defined('_THEME_MOBILE_DIR_')) {
                $file = str_replace('{MOBILE}', _THEME_MOBILE_DIR_, $file);
            }
            if (defined('_THEME_MOBILE_CSS_DIR_')) {
                $file = str_replace('{MOBILE_CSS}', _THEME_MOBILE_CSS_DIR_, $file);
            }
            if (defined('_PS_THEME_OVERRIDE_DIR_')) {
                $file = str_replace('{OVERRIDE}', _PS_THEME_OVERRIDE_DIR_, $file);
            }
        }
        $this->context->controller->addCSS($file);
    }

    // Hooks
    /**
     * @throws PrestaShopException
     */
    public function hookDisplayHeader()
    {
        $this->addCSSFile($this->_path.'views/css/front.css');
        $this->addCSSFile(Configuration::get(static::MOLLIE_CSS));
    }

    /**
     * @throws PrestaShopException
     */
    public function hookDisplayBackOfficeHeader()
    {
        if ($this->context->controller instanceof AdminOrdersController && version_compare(_PS_VERSION_, '1.6.0.0', '<')
            || $this->context->controller instanceof AdminModulesController && Tools::getValue('configure') === $this->name
        ) {
            $this->addCSSFile(Configuration::get(static::MOLLIE_CSS));
        }
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws Adapter_Exception
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayAdminOrder($params)
    {
        $cartId = Cart::getCartIdByOrderId((int) $params['id_order']);

        $mollieData = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            sprintf(
                'SELECT * FROM `%s` WHERE `cart_id` = \'%s\' ORDER BY `created_at` DESC',
                _DB_PREFIX_.'mollie_payments',
                (int) $cartId
            )
        );
        // If the order_id is NULL in the mollie_payments db table
        // use Order::getOrderByCartId for backwards compatibility
        if (empty($mollieData['order_id'])) {
            $mollieData['order_id'] = Order::getOrderByCartId((int) $cartId);
        }

        if (Tools::isSubmit('Mollie_Refund')) {
            $tplData = $this->doRefund((int) $mollieData['order_id'], $mollieData['transaction_id']);
            if ($tplData['status'] === 'success') {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true).'&vieworder&id_order='.(int) $params['id_order']);
            }
        } elseif (isset($mollieData['bank_status']) && $mollieData['bank_status'] === \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED) {
            $tplData = array(
                'status'      => 'success',
                'msg_success' => $this->lang('The order has been refunded!'),
                'msg_details' => $this->lang(
                    'Mollie B.V. will transfer the money back to the customer on the next business day.'
                ),
            );
        } elseif (isset($mollieData['bank_status']) && in_array($mollieData['bank_status'], array(
            \Mollie\Api\Types\PaymentStatus::STATUS_PAID, \Mollie\Api\Types\SettlementStatus::STATUS_PAIDOUT))) {
            $tplData = array(
                'status'          => 'form',
                'msg_button'      => $this->lang['Refund this order'],
                'msg_description' => sprintf(
                    $this->lang['Refund order #%d through the Mollie API.'],
                    (int) $mollieData['order_id']
                ),
            );
        } else {
            return '';
        }

        $tplData['msg_title'] = $this->lang['Mollie refund'];
        $tplData['img_src'] = $this->_path.'views/img/logo_small.png';
        $this->context->controller->addJS($this->_path.'views/js/app/dist/confirmrefund.min.js');
        $this->smarty->assign($tplData);
        $this->context->smarty->assign(array(
            'link'       => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__.'modules/'.basename(__FILE__, '.php').'/',
        ));

        return $this->display(__FILE__, 'refund.tpl');
    }

    /**
     * @return string
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayPayment()
    {
        $smarty = $this->context->smarty;
        $issuerSetting = Configuration::get(static::MOLLIE_ISSUERS);
        $issuerList = in_array(
            $issuerSetting,
            array(static::ISSUERS_ON_CLICK)
        )
            ? $this->getIssuerList()
            : array();

        try {
            $apiMethods = $this->getFilteredApiMethods();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $apiMethods = array();
            $issuerList = array();

            if (Configuration::get(static::MOLLIE_DEBUG_LOG) == static::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: '.$e->getMessage(), static::ERROR);
            }
            if (Configuration::get(static::MOLLIE_DISPLAY_ERRORS)) {
                $smarty->assign('message', $e->getMessage());

                return $this->display(__FILE__, 'error_message.tpl');
            }
        }

        Media::addJsDef(array('mollieQrEnabled' => (bool) Configuration::get(static::MOLLIE_QRENABLED)));
        $cart = Context::getContext()->cart;
        $smarty->assign(
            array(
                'link'                  => $this->context->link,
                'cartAmount'            => (int) ($cart->getOrderTotal(true) * 100),
                'methods'               => $apiMethods,
                'issuers'               => $issuerList,
                'issuer_setting'        => $issuerSetting,
                'warning'               => $this->warning,
                'msg_pay_with'          => $this->lang['Pay with %s'],
                'msg_bankselect'        => $this->lang['Select your bank:'],
                'module'                => $this,
                'mollie_banks_app_path' => static::getMediaPath($this->_path.'views/js/app/dist/banks.min.js'),
                'mollie_translations'   => array(
                    'chooseYourBank' => $this->l('Choose your bank'),
                    'orPayByIdealQr' => $this->l('or pay by iDEAL QR'),
                    'choose'         => $this->l('Choose'),
                    'cancel'         => $this->l('Cancel'),
                ),
            )
        );

        return $this->display(__FILE__, 'payment.tpl');
    }

    /**
     * EU Advanced Compliance module (PrestaShop module) Advanced Checkout option enabled
     *
     * @return array|null
     *
     * @throws PrestaShopException
     */
    public function hookDisplayPaymentEU()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return array();
        }

        try {
            $methods = $this->getFilteredApiMethods();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            if (Configuration::get(static::MOLLIE_DEBUG_LOG) == static::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__." said: {$e->getMessage()}", static::ERROR);
            }

            return array();
        }

        $iso = strtolower(Context::getContext()->currency->iso_code);
        $paymentOptions = array();
        foreach ($methods as $method) {
            if (!isset(static::$methodCurrencies[$method->id])) {
                continue;
            }
            if (in_array($iso, static::$methodCurrencies[$method->id])) {
                continue;
            }
            
            $paymentOptions[] = array(
                'cta_text' => $this->lang[$method->description],
                'logo'     => $method->image->size1x,
                'action'   => $this->context->link->getModuleLink(
                    'mollie',
                    'payment',
                    array('method' => $method->id),
                    true
                ),
            );
        }

        return $paymentOptions;
    }

    /**
     * @param $params
     *
     * @return array|null
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookPaymentOptions()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return array();
        }

        try {
            $methods = $this->getFilteredApiMethods();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: '.$e->getMessage(), Mollie::ERROR);
            }

            return array();
        }

        $idealIssuers = array();
        $issuers = $this->getIssuerList();
        if (isset($issuers['ideal'])) {
            foreach ($issuers['ideal'] as $issuer) {
                $idealIssuers[$issuer->id] = $issuer;
            }
        }

        $context = Context::getContext();
        $cart = $context->cart;

        $context->smarty->assign(array(
            'idealIssuers'  => $idealIssuers,
            'link'          => $this->context->link,
            'qrCodeEnabled' => true,
            'qrAlign'       => 'left',
            'cartAmount'    => (int) ($cart->getOrderTotal(true) * 100),
        ));

        $iso = strtolower($context->currency->iso_code);
        $paymentOptions = array();
        foreach ($methods as $method) {
            if (!isset(static::$methodCurrencies[$method->id])) {
                continue;
            }
            if (!in_array($iso, static::$methodCurrencies[$method->id])) {
                continue;
            }

            if ($method->id === 'ideal' && Configuration::get(static::MOLLIE_ISSUERS) == static::ISSUERS_ON_CLICK) {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang[$method->description])
                    ->setAction(Context::getContext()->link->getModuleLink(
                        $this->name,
                        'payment',
                        array('method' => $method->id),
                        true
                    ))
                    ->setInputs(array(
                        'token' => array(
                            'name'  => 'issuer',
                            'type'  => 'hidden',
                            'value' => '',
                        ),
                    ))
                    ->setLogo($method->image->size1x)
                    ->setAdditionalInformation($this->display(__FILE__, 'ideal_dropdown.tpl'))
                ;

                $paymentOptions[] = $newOption;
            } else {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                if (isset($this->lang[$method->description])) {
                    $description = $this->lang[$method->description];
                } else {
                    $description = $method->description;
                }
                $newOption
                    ->setCallToActionText($description)
                    ->setAction(Context::getContext()->link->getModuleLink(
                        'mollie', 'payment',
                        array('method' => $method->id), true
                    ))
                    ->setLogo($method->image->size1x)
                ;

                $paymentOptions[] = $newOption;
            }
        }

        return $paymentOptions;
    }

    /**
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayOrderConfirmation()
    {
        $payment = $this->getPaymentBy('cart_id', (int) Tools::getValue('id_cart'));
        if ($payment && $payment['bank_status'] == \Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
            $this->context->smarty->assign('okMessage', $this->lang('Thank you. Your payment has been received.'));

            return $this->display(__FILE__, 'ok.tpl');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function addCartIdChangePrimaryKey()
    {
        $sql = sprintf(
            '
			ALTER TABLE `%1$s` DROP PRIMARY KEY;
			ALTER TABLE `%1$s` ADD PRIMARY KEY (`transaction_id`),
				ADD COLUMN `cart_id` INT(64),
				ADD KEY (`cart_id`);',
            _DB_PREFIX_.'mollie_payments'
        );

        try {
            if (!Db::getInstance()->execute($sql)) {
                $this->_errors[] = 'Database error: '.Db::getInstance()->getMsgError();

                return false;
            }
        } catch (PrestaShopException $e) {
            $this->_errors[] = 'Database error: '.Db::getInstance()->getMsgError();

            return false;
        }

        return true;
    }

    /**
     * Create new order state while mollie payment pending.
     *
     * @since 2.0.0
     *
     * @return boolean
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function pendingOrderState()
    {
        $stateExist = false;
        $states = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($states as $state) {
            if (in_array($this->lang('Awaiting Mollie payment'), $state)) {
                $stateExist = true;
                break;
            }
        }
        if (!$stateExist) {
            $orderState = new OrderState();
            $orderState->send_email = false;
            $orderState->color = '#4169E1';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->name;
            $orderState->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $orderState->name[$language['id_lang']] = $this->lang('Awaiting Mollie payment');
            }
            if ($orderState->add()) {
                $source = _PS_MODULE_DIR_.'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $orderState->id.'.gif';
                @copy($source, $destination);
            }
            Configuration::updateValue(static::MOLLIE_PENDING, (int) $orderState->id);
        }

        return true;
    }

    /**
     * Create new order state for partial refunds.
     *
     * @since 2.0.0
     *
     * @return boolean
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function partialRefundOrderState()
    {
        $stateExist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($states as $state) {
            if (in_array($this->lang('Mollie Partially Refunded'), $state)) {
                $stateExist = true;
                break;
            }
        }
        if (!$stateExist) {
            $orderState = new OrderState();
            $orderState->send_email = false;
            $orderState->color = '#6F8C9F';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->name;
            $orderState->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $orderState->name[$language['id_lang']] = $this->lang('Mollie partially refunded');
            }
            if ($orderState->add()) {
                $source = _PS_MODULE_DIR_.'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $orderState->id.'.gif';
                @copy($source, $destination);
            }
            Configuration::updateValue(static::MOLLIE_STATUS_PARTIAL_REFUND, (int) $orderState->id);
        }

        return true;
    }

    /**
     * @param string      $mediaUri
     * @param string|null $cssMediaType
     *
     * @return array|bool|mixed|string
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getMediaPath($mediaUri, $cssMediaType = null)
    {
        if (is_array($mediaUri) || $mediaUri === null || empty($mediaUri)) {
            return false;
        }

        $urlData = parse_url($mediaUri);
        if (!is_array($urlData)) {
            return false;
        }

        if (!array_key_exists('host', $urlData)) {
            $mediaUri = '/'.ltrim(str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
            // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
            $fileUri = _PS_ROOT_DIR_.Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

            if (!@filemtime($fileUri) || @filesize($fileUri) === 0) {
                return false;
            }

            $mediaUri = str_replace('//', '/', $mediaUri);
        }

        if ($cssMediaType) {
            return array($mediaUri => $cssMediaType);
        }

        return $mediaUri;
    }

    /**
     * Get payment data
     *
     * @param float|string $amount
     * @param              $currency
     * @param string       $method
     * @param string|null  $issuer
     * @param int|Cart     $cartId
     * @param string       $secureKey
     * @param bool         $qrCode
     *
     * @return array
     * @throws PrestaShopException
     */
    public static function getPaymentData($amount, $currency, $method, $issuer, $cartId, $secureKey, $qrCode = false)
    {
        $description = static::generateDescriptionFromCart($cartId);
        $context = Context::getContext();

        $paymentData = array(
            'amount'      => array(
                'currency' => $currency ? strtoupper($currency) : 'EUR',
                'value'    => number_format(str_replace(',', '.', $amount), 2),
            ),
            'method'      => $method,
            'issuer'      => $issuer,
            'description' => str_replace(
                '%',
                $cartId,
                $description
            ),
            'redirectUrl' => ($qrCode
                ? $context->link->getModuleLink(
                    'mollie',
                    'qrcode',
                    array('cart_id' => $cartId, 'done' => 1)
                )
                : $context->link->getModuleLink(
                    'mollie',
                    'return',
                    array('cart_id' => $cartId, 'utm_nooverride' => 1)
                )
            ),
            'webhookUrl'  => $context->link->getModuleLink(
                'mollie',
                'webhook'
            ),
        );

        $paymentData['metadata'] = array(
            'cart_id'    => $cartId,
            'secure_key' => Tools::encrypt($secureKey),
        );

        // Send webshop locale
        if (Configuration::get(
                Mollie::MOLLIE_PAYMENTSCREEN_LOCALE
            ) === Mollie::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE
        ) {
            $locale = static::getWebshopLocale();

            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                $paymentData['locale'] = $locale;
            }
        }

        if (isset($context->cart)) {
            if (isset($context->cart->id_customer)) {
                $buyer = new Customer($context->cart->id_customer);
                $paymentData['billingEmail'] = (string) $buyer->email;
            }
            if (isset($context->cart->id_address_invoice)) {
                $billing = new Address((int) $context->cart->id_address_invoice);
                $paymentData['billingAddress'] = array(
                    'streetAndNumber' => (string) $billing->address1.' '.$billing->address2,
                    'city'            => (string) $billing->city,
                    'region'          => (string) State::getNameById($billing->id_state),
                    'postalCode'      => (string) $billing->postcode,
                    'country'         => (string) Country::getIsoById($billing->id_country),
                );
            }
            if (isset($context->cart->id_address_delivery)) {
                $shipping = new Address((int) $context->cart->id_address_delivery);
                $paymentData['billingAddress'] = array(
                    'streetAndNumber' => (string) $shipping->address1.' '.$shipping->address2,
                    'city'            => (string) $shipping->city,
                    'region'          => (string) State::getNameById($shipping->id_state),
                    'postalCode'      => (string) $shipping->postcode,
                    'country'         => (string) Country::getIsoById($shipping->id_country),
                );
            }
        }

        return $paymentData;
    }

    /**
     * Generate a description from the Cart
     *
     * @param Cart|int $cartId Cart or Cart ID
     *
     * @return string Description
     *
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    public static function generateDescriptionFromCart($cartId)
    {
        if ($cartId instanceof Cart) {
            $cart = $cartId;
        } else {
            $cart = new Cart($cartId);
        }

        $buyer = null;
        if ($cart->id_customer) {
            $buyer = new Customer($cart->id_customer);
        }

        $filters = array(
            'cart.id'            => $cartId,
            'customer.firstname' => $buyer == null ? '' : $buyer->firstname,
            'customer.lastname'  => $buyer == null ? '' : $buyer->lastname,
            'customer.company'   => $buyer == null ? '' : $buyer->company,
        );

        $content = Configuration::get(Mollie::MOLLIE_DESCRIPTION);

        foreach ($filters as $key => $value) {
            $content = str_replace(
                "{".$key."}",
                $value,
                $content
            );
        }

        return $content;
    }

    /**
     * Get webshop locale
     *
     * @return string
     *
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    public static function getWebshopLocale()
    {
        // Current language
        if (Context::getContext()->language instanceof Language) {
            $language = Context::getContext()->language->iso_code;
        } else {
            $language = 'en';
        }
        $supportedLanguages = array(
            'de',
            'en',
            'es',
            'fr',
            'nl',
        );
        $supportedLocales = array(
            'en_US',
            'de_AT',
            'de_CH',
            'de_DE',
            'es_ES',
            'fr_BE',
            'fr_FR',
            'nl_BE',
            'nl_NL',
        );

        $langIso = Tools::strtolower($language);
        if (!in_array($langIso, $supportedLanguages)) {
            $langIso = 'en';
        }
        $countryIso = Tools::strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
        if (!in_array("{$langIso}_{$countryIso}", $supportedLocales)) {
            switch ($langIso) {
                case 'de':
                    $countryIso = 'DE';
                    break;
                case 'es':
                    $countryIso = 'ES';
                    break;
                case 'fr':
                    $countryIso = 'FR';
                    break;
                case 'nl':
                    $countryIso = 'NL';
                    break;
                default:
                    $countryIso = 'US';
            }
        }

        return "{$langIso}_{$countryIso}";
    }

    /**
     * Ajax process download module update
     *
     * @since 3.0.0
     * @throws ErrorException
     */
    public function ajaxProcessDownloadUpdate()
    {
        @ob_clean();
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $latestVersion = $this->getLatestVersion();
        } catch (PrestaShopException $e) {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            )));
        } catch (SmartyException $e) {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            )));
        }
        if (version_compare(substr($latestVersion['version'], 1, strlen($latestVersion['version']) - 1), $this->version, '>')) {
            // Then update
            die(json_encode(array(
                'success' => $this->downloadModuleFromLocation($this->name, $latestVersion['download']),
            )));
        } else {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('You are already running the latest version!'),
            )));
        }
    }

    /**
     * Ajax process install module update
     *
     * @since 3.0.0
     */
    public function ajaxProcessInstallUpdate()
    {
        @ob_clean();
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->unzipModule();
        } catch (Adapter_Exception $e) {
            $result = false;
        } catch (PrestaShopDatabaseException $e) {
            $result = false;
        } catch (PrestaShopException $e) {
            $result = false;
        }

        die(json_encode(array(
            'success' => $result,
            'message'  => isset($this->context->controller->errors[0]) ? $this->context->controller->errors[0] : '',
        )));
    }

    /**
     * Ajax process run module upgrade
     *
     * @since 3.0.0
     */
    public function ajaxProcessRunUpgrade()
    {
        @ob_clean();
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->runUpgradeModule();
        } catch (PrestaShopDatabaseException $e) {
            $error = $e->getMessage();
            $result = false;
        } catch (PrestaShopException $e) {
            $error = $e->getMessage();
            $result = false;
        }

        die(json_encode(array(
            'success' => $result,
            'message' => isset($error) ? $error : '',
        )));
    }

    /**
     * Download the latest module from the given location
     *
     * @param string $moduleName
     * @param string $location
     *
     * @return bool
     * @throws ErrorException
     */
    protected function downloadModuleFromLocation($moduleName, $location)
    {
        $zipLocation = _PS_MODULE_DIR_.$moduleName.'.zip';
        if (@!file_exists($zipLocation)) {
            $curl = new \Curl\Curl();
            $curl->setOpt(CURLOPT_ENCODING , '');
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            if (!$curl->download($location, _PS_MODULE_DIR_.'mollie-update.zip')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Unzip the module
     *
     * @return bool Whether the module has been successfully extracted
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    protected function unzipModule()
    {
        if (@file_exists(_PS_MODULE_DIR_.'mollie-update.zip')) {
            return $this->extractModuleArchive($this->name, _PS_MODULE_DIR_.'mollie-update.zip');
        }

        return false;
    }

    /**
     * Extracts a module archive to the `modules` folder
     *
     * @param string $moduleName Module name
     * @param string $file       File source location
     *
     * @return bool
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    protected function extractModuleArchive($moduleName, $file)
    {
        $zipFolders = array();
        $tmpFolder = _PS_MODULE_DIR_.$moduleName.md5(time());

        if (@!file_exists($file)) {
            $this->context->controller->errors[] = $this->l('Module archive could not be downloaded');

            return false;
        }

        $success = false;
        if (substr($file, -4) == '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder.DIRECTORY_SEPARATOR.$moduleName)) {
                if (file_exists(_PS_MODULE_DIR_.$moduleName)) {
                    if (!ConfigurationTest::testDir(_PS_MODULE_DIR_.$moduleName, true, $report, true)) {
                        $this->context->controller->errors[] = sprintf($this->l('Could not update module `%s`: module directory not writable (`%s`).'), $moduleName, $report);
                        $this->recursiveDeleteOnDisk($tmpFolder);
                        @unlink(_PS_MODULE_DIR_.$moduleName.'.zip');

                        return false;
                    }
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$moduleName);
                }
                if (@rename($tmpFolder.DIRECTORY_SEPARATOR.$moduleName, _PS_MODULE_DIR_.$moduleName)) {
                    $success = true;
                }
            }
        }

        if (!$success) {
            $this->context->controller->errors[] = $this->l('There was an error while extracting the module file (file may be corrupted).');
            // Force a new check
        } else {
            //check if it's a real module
            foreach ($zipFolders as $folder) {
                if (!in_array($folder, array('.', '..', '.svn', '.git', '__MACOSX')) && !Module::getInstanceByName($folder)) {
                    $this->context->controller->errors[] = sprintf($this->l('The module %1$s that you uploaded is not a valid module.'), $folder);
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$folder);
                }
            }
        }

        @unlink($file);
        @unlink(_PS_MODULE_DIR_.$moduleName.'backup');
        $this->recursiveDeleteOnDisk($tmpFolder);

        die(json_encode(array(
            'success' => $success,
        )));
    }

    /**
     * Delete folder recursively
     *
     * @param string $dir Directory
     *
     * @since 3.0.0
     */
    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        $this->recursiveDeleteOnDisk($dir.'/'.$object);
                    } else {
                        @unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Get payment methods to show on the configuration page
     *
     * @param bool $active Active methods only
     *
     * @return array
     *
     * @throws PrestaShopException
     * @since 3.0.0
     */
    protected function getMethodsForConfig($active = false)
    {
        try {
            $apiMethods = $this->api->methods->all();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $apiMethods = array();
        } catch (Exception $e) {
            $apiMethods = array();
        }
        if (!count($apiMethods)) {
            return array();
        }

        $dbMethods = @json_decode(Configuration::get(static::METHODS_CONFIG), true);
        if (!is_array($dbMethods)) {
            $configMethods = array();
        } else {
            $configMethods = array();
            foreach ($dbMethods as $dbMethod) {
                $configMethods[$dbMethod['id']] = $dbMethod;
            }
        }

        $methodsFromDb = array_keys($configMethods);
        $methods = array();
        $deferredMethods = array();
        foreach ($apiMethods as $apiMethod) {
            if (!in_array($apiMethod->id, $methodsFromDb)) {
                $deferredMethods[] = array(
                    'id'      => $apiMethod->id,
                    'name'    => $apiMethod->description,
                    'image'   => $apiMethod->image->size2x,
                    'enabled' => true,
                );
            } else {
                $methods[$configMethods[$apiMethod->id]['position']] = array(
                    'id'      => $apiMethod->id,
                    'name'    => $apiMethod->description,
                    'enabled' => $configMethods[$apiMethod->id]['enabled'],
                    'image'   => $apiMethod->image->size2x,
                );
            }
        }
        ksort($methods);
        $methods = array_values($methods);
        foreach ($deferredMethods as $deferredMethod) {
            $methods[] = $deferredMethod;
        }
        if ($active) {
            foreach ($methods as $index => $method) {
                if (!$method['enabled']) {
                    unset($methods[$index]);
                }
            }
        }

        return $methods;
    }

    /**
     * Get filtered API method
     *
     * @return array
     *
     * @throws PrestaShopException
     * @throws \Mollie\Api\Exceptions\ApiException
     *
     * @since 3.0.0
     */
    protected function getFilteredApiMethods()
    {
        $iso = strtolower($this->context->currency->iso_code);
        $dbMethods = $this->getMethodsForConfig(true);
        $methods = array();
        $apiMethods = $this->api->methods->all()->getArrayCopy();
        foreach ($dbMethods as $method) {
            foreach ($apiMethods as $apiMethod) {
                if ($apiMethod->id === $method['id']) {
                    $methods[] = $apiMethod;
                    break;
                }
            }
        }
        foreach ($methods as $index => $method) {
            if (!isset(static::$methodCurrencies[$method->id])) {
                continue;
            }
            if (!in_array($iso, static::$methodCurrencies[$method->id])) {
                unset($methods[$index]);
            }
        }

        return $methods;
    }
}
