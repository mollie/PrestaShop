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
        $this->version = '2.0.6';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mollie Payment Module');
        $this->description = $this->l('Mollie Payments');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Mollie Payment Module?');

        $this->controllers = array('payment', 'return', 'webhook');

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
        Configuration::deleteByName(static::MOLLIE_IMAGES);
        Configuration::deleteByName(static::MOLLIE_ISSUERS);
        Configuration::deleteByName(static::MOLLIE_CSS);
        Configuration::deleteByName(static::MOLLIE_DEBUG_LOG);
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
        Configuration::updateGlobalValue(static::MOLLIE_IMAGES, static::LOGOS_NORMAL);
        Configuration::updateGlobalValue(static::MOLLIE_ISSUERS, static::ISSUERS_ON_CLICK);
        Configuration::updateGlobalValue(static::MOLLIE_CSS, '');
        Configuration::updateGlobalValue(static::MOLLIE_DEBUG_LOG, static::DEBUG_LOG_ERRORS);
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

        $updateMessage = defined('_TB_VERSION_')
            ? $this->getUpdateMessage('https://github.com/mollie/thirtybees')
            : $this->getUpdateMessage('https://github.com/mollie/PrestaShop');
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
            'all_statuses'             => OrderState::getOrderStates($lang),
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
            'val_images'               => Configuration::get(static::MOLLIE_IMAGES),
            'val_issuers'              => Configuration::get(static::MOLLIE_ISSUERS),
            'val_css'                  => Configuration::get(static::MOLLIE_CSS),
            'val_errors'               => Configuration::get(static::MOLLIE_DISPLAY_ERRORS),
            'val_logger'               => Configuration::get(static::MOLLIE_DEBUG_LOG),
            'val_save'                 => $this->l('Save'),
            'lang'                     => $this->lang,
        );

        $messageStatus = $this->l('Status for %s payments');
        $descriptionStatus = $this->l('`%s` payments get status `%s`');
        $messageMail = $this->l('Send mails when %s');
        $descriptionMail = $this->l('Send mails when transaction status becomes %s?');
        foreach ($this->statuses as $name => $val) {
            $val = (int) $val;
            $data['msg_status_'.$name] = sprintf($messageStatus, $this->lang[$name]);
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
            $data['val_status_'.$name] = $val;
            $data['msg_mail_'.$name] = sprintf($messageMail, $this->lang[$name]);
            $data['desc_mail_'.$name] = sprintf($descriptionMail, $this->lang[$name]);
            $data['val_mail_'.$name] = Configuration::get('MOLLIE_MAIL_WHEN_'.Tools::strtoupper($name));
            $data['statuses'][] = $name;
        }

        $this->context->smarty->assign($data);
        $this->context->smarty->assign(array(
            'link'       => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__.'modules/'.basename(__FILE__, '.php').'/',
        ));

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
            Configuration::updateValue(static::MOLLIE_IMAGES, $mollieImages);
            Configuration::updateValue(static::MOLLIE_ISSUERS, $mollieIssuers);
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
     * @param string $url
     *
     * @return string
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
                    $latestVersion = preg_replace("/[^0-9,.]/", "", Tools::substr($title, strrpos($title, '/')));
                    if (!version_compare($this->version, $latestVersion, '>=')) {
                        $this->context->smarty->assign(array(
                            'release_url'     => $url,
                            'this_version'    => $this->version,
                            'release_version' => $latestVersion,
                        ));
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
            $this->api->payments->refund($payment);
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
                $file = $this->_path.'views/css/mollie_bootstrap.css';
            } else {
                // Use default css file
                $file = $this->_path.'views/css/mollie.css';
            }
        } else {
            // Use a custom css file
            $file = str_replace('{BASE}', _PS_BASE_URL_, $file);
            $file = str_replace('{THEME}', _PS_THEME_DIR_, $file);
            $file = str_replace('{CSS}', _PS_CSS_DIR_, $file);
            $file = str_replace('{MOBILE}', _THEME_MOBILE_DIR_, $file);
            $file = str_replace('{MOBILE_CSS}', _THEME_MOBILE_CSS_DIR_, $file);
            $file = str_replace('{OVERRIDE}', _PS_THEME_OVERRIDE_DIR_, $file);
        }
        $this->context->controller->addCSS($file);
    }

    // Hooks
    /**
     * @throws PrestaShopException
     */
    public function hookDisplayHeader()
    {
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

        /*if (!Currency::exists('EUR', 0)) {
            $smarty->assign('message', $this->l('Mollie Payment Methods are only available when Euros are activated.'));

            return $this->display(__FILE__, 'error_message.tpl');
        }*/

        $issuerSetting = Configuration::get(static::MOLLIE_ISSUERS);

        try {
            $methods = $this->api->methods->all();
            $issuerList = in_array(
                $issuerSetting,
                array(static::ISSUERS_ON_CLICK)
            )
                ? $this->getIssuerList()
                : array();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $methods = array();
            $issuerList = array();

            if (Configuration::get(static::MOLLIE_DEBUG_LOG) == static::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: '.$e->getMessage(), static::ERROR);
            }
            if (Configuration::get(static::MOLLIE_DISPLAY_ERRORS)) {
                $smarty->assign('message', $e->getMessage());

                return $this->display(__FILE__, 'error_message.tpl');
            }
        }

        $smarty->assign(
            array(
                'methods'               => $methods->getArrayCopy(),
                'issuers'               => $issuerList,
                'issuer_setting'        => $issuerSetting,
                'images'                => Configuration::get(static::MOLLIE_IMAGES),
                'warning'               => $this->warning,
                'msg_pay_with'          => $this->lang['Pay with %s'],
                'msg_bankselect'        => $this->lang['Select your bank:'],
                'module'                => $this,
                'mollie_banks_app_path' => static::getMediaPath($this->_path.'views/js/app/dist/banks.min.js'),
                'mollie_translations'   => array(
                    'chooseYourBank' => $this->l('Choose your bank'),
                    'choose'         => $this->l('Choose'),
                    'cancel'         => $this->l('Cancel'),
                ),
            )
        );

        return $this->display(__FILE__, 'payment.tpl');
    }

    /**
     * EU Advanced Compliance module (prestahop module) Advanced Checkout option enabled
     *
     * @return array|null
     *
     * @throws PrestaShopException
     */
    public function hookDisplayPaymentEU()
    {
        if (/*!Currency::exists('EUR', 0) ||*/ version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return array();
        }

        try {
            $methods = $this->api->methods->all();
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            if (Configuration::get(static::MOLLIE_DEBUG_LOG) == static::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: '.$e->getMessage(), static::ERROR);
            }

            return array();
        }

        $paymentOptions = array();
        foreach ($methods as $method) {
            $paymentOptions[] = array(
                'cta_text' => $this->lang[$method->description],
                'logo'     => $method->image->normal,
                'action'   => $this->context->link->getModuleLink(
                    'mollie', 'payment',
                    array('method' => $method->id), true
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

        return include dirname(__FILE__).'/lib/paymentoptions.php';
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
        $states = OrderState::getOrderStates((int)$this->context->language->id);
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
                $source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$orderState->id . '.gif';
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
                $source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$orderState->id . '.gif';
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
}
