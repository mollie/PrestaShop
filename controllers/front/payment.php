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

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MolliePaymentModuleFrontController
 *
 * @property Context|null $context
 * @property Mollie       $module
 */
class MolliePaymentModuleFrontController extends ModuleFrontController
{
    // @codingStandardsIgnoreStart
    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    // @codingStandardsIgnoreEnd

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        /** @var Cart $cart */
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);

        if (!$this->validate(
            $cart,
            $customer
        )) {
            $this->errors[] = $this->module->lang['This payment method is not available.'];
            $this->setTemplate('error.tpl');

            return;
        }

        $method = Tools::getValue('method');
        $issuer = Tools::getValue('issuer') ?: null;

        // If no issuer was set yet and the issuer list has its own page, show issuer list here
        if (!$issuer && Configuration::get(Mollie::MOLLIE_ISSUERS) == Mollie::ISSUERS_OWN_PAGE && $method === 'ideal') {
            $tplData = array();
            $issuers = $this->module->getIssuerList();
            $tplData['issuers'] = isset($issuers['ideal']) ? $issuers['ideal'] : array();
            if (!empty($tplData['issuers'])) {
                $tplData['msg_bankselect'] = $this->module->lang['Select your bank:'];
                $tplData['msg_ok'] = $this->module->lang['OK'];
                $tplData['msg_return'] = $this->module->lang['Different payment method'];
                $this->context->smarty->assign($tplData);
                if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                    $this->setTemplate('mollie_issuers.tpl');
                } else {
                    $this->setTemplate('module:mollie/views/templates/front/mollie_issuers17.tpl');
                }

                return;
            }
        }

        // Currency conversion (thou shalt pay in euros)
        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $amount = $originalAmount;

        // Prepare payment
        $paymentData = $this->getPaymentData(
            $amount,
            $method,
            $issuer,
            (int) $cart->id,
            $customer->secure_key
        );
        $payment = $this->createPayment($paymentData);

        // Store payment linked to cart
        if ($payment->method != 'banktransfer') {
            Db::getInstance()->insert(
                'mollie_payments',
                array(
                    'cart_id'        => (int) $cart->id,
                    'method'         => $payment->method,
                    'transaction_id' => $payment->id,
                    'bank_status'    => \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                    'created_at'     => date("Y-m-d H:i:s"),
                )
            );
        }

        if ($payment->method == 'banktransfer') {
            $this->module->validateOrder(
                (int) $cart->id,
                $this->module->statuses[$payment->status],
                $originalAmount,
                isset(Mollie::$methods[$payment->method]) ? Mollie::$methods[$payment->method] : 'Mollie',
                null,
                array(),
                null,
                false,
                $customer->secure_key
            );

            $orderId = Order::getOrderByCartId((int) $cart->id);

            Db::getInstance()->insert(
                'mollie_payments',
                array(
                    'cart_id'        => (int) $cart->id,
                    'order_id'       => $orderId,
                    'method'         => $payment->method,
                    'transaction_id' => $payment->id,
                    'bank_status'    => \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                    'created_at'     => date("Y-m-d H:i:s"),
                )
            );
        }

        // Go to payment url
        Tools::redirect($payment->getCheckoutUrl());
    }

    /**
     * Checks if this payment option is still available
     * May redirect the user to a more appropriate page
     *
     * @param Cart     $cart
     * @param Customer $customer
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function validate($cart, $customer)
    {
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
            // We be like: how did you even get here?
            Tools::redirect(Context::getContext()->link->getPageLink('index', true));
            return false;
        }

        $authorized = false;

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'mollie') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            return false;
        }

        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves a list of issuers for the selected method
     *
     * @param string $method
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    private function getIssuerListByMethod($method)
    {
        try {
            $issuers = $this->module->api->issuers->all();
            $issuerList = array();
            foreach ($issuers as $issuer) {
                if ($issuer->method === $method) {
                    $issuerList[$issuer->id] = $issuer->name;
                }
            }
            return $issuerList;
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: '.$e->getMessage(), Mollie::NOTICE);
            }
        }
        return array();
    }

    /**
     * @param float $amount
     *
     * @return float
     *
     * @throws PrestaShopException
     */
   /* private function convertCurrencyToEuro($amount)
    {
        $cart = $this->context->cart;
        $currencyEuro = Currency::getIdByIsoCode('EUR');
        if (!$currencyEuro) {
            // No Euro currency available!
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(
                    __METHOD__.' said: In order to use this module, you need to enable Euros as currency.',
                    Mollie::CRASH
                );
            }
            die($this->module->lang['This payment method is only available for Euros.']);
        }

        if ($cart->id_currency !== $currencyEuro) {
            // Convert non-euro currency to default
            $amount = Tools::convertPrice(
                $amount,
                $cart->id_currency,
                false
            );

            if (Currency::getDefaultCurrency() !== $currencyEuro) {
                // If default is not euro, convert to euro
                $amount = Tools::convertPrice(
                    $amount,
                    $currencyEuro,
                    true
                );
            }
        }

        return round(
            $amount,
            2
        );
    }*/

    /**
     * @param int $cartId
     *
     * @return string
     * @throws PrestaShopException
     */
    private function generateDescriptionFromCart($cartId)
    {
        $cart = new Cart($cartId);

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
     * @param float|string $amount
     * @param string       $method
     * @param string|null  $issuer
     * @param int          $cartId
     * @param string       $secureKey
     *
     * @return array
     * @throws PrestaShopException
     */
    private function getPaymentData($amount, $method, $issuer, $cartId, $secureKey)
    {
        $description = $this->generateDescriptionFromCart($cartId);

        $currency = Currency::getCurrency((int) $this->context->cart->id_currency);
        $currencyIso = $currency['iso_code'];

        $paymentData = array(
            'amount'      => array(
                'currency' => $currencyIso ? strtoupper($currencyIso) : 'EUR',
                'value'    => number_format(str_replace(',', '.', $amount), 2),
            ),
            'method'      => $method,
            'issuer'      => $issuer,
            'description' => str_replace(
                '%',
                $cartId,
                $description
            ),
            'redirectUrl' => $this->context->link->getModuleLink(
                'mollie',
                'return',
                array('cart_id' => $cartId, 'utm_nooverride' => 1)
            ),
            'webhookUrl'  => $this->context->link->getModuleLink(
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
            $locale = $this->getWebshopLocale();

            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                $paymentData['locale'] = $locale;
            }
        }

        if (isset($this->context, $this->context->cart)) {
            if (isset($this->context->cart->id_customer)) {
                $buyer = new Customer($this->context->cart->id_customer);
                $paymentData['billingEmail'] = (string) $buyer->email;
            }
            if (isset($this->context->cart->id_address_invoice)) {
                $billing = new Address((int) $this->context->cart->id_address_invoice);
                $paymentData['billingAddress'] = array(
                    'streetAndNumber' => (string) $billing->address1.' '.$billing->address2,
                    'city'            => (string) $billing->city,
                    'region'          => (string) State::getNameById($billing->id_state),
                    'postalCode'      => (string) $billing->postcode,
                    'country'         => (string) Country::getIsoById($billing->id_country),
                );
            }
            if (isset($this->context->cart->id_address_delivery)) {
                $shipping = new Address((int) $this->context->cart->id_address_delivery);
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
     * @return string
     * @throws PrestaShopException
     */
    private function getWebshopLocale()
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
     * @param array $data
     *
     * @return \Mollie\Api\Resources\Payment|null
     *
     * @throws PrestaShopException
     */
    private function createPayment($data)
    {
        $payment = null;
        if (Configuration::get(Mollie::MOLLIE_USE_PROFILE_WEBHOOK)) {
            unset($data['webhookUrl']);
        }

//        try {
            /** @var \Mollie\Api\Resources\Payment $payment */
            $payment = $this->module->api->payments->create($data);
//        } catch (\Mollie\Api\Exceptions\ApiException $e) {
//            try {
//                if ($e->getField() === 'webhookUrl') {
//                    if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
//                        Logger::addLog(
//                            __METHOD__.' said: Could not reach generated webhook url, falling back to profile webhook url.',
//                            Mollie::WARNING
//                        );
//                    }
//                    unset($data['webhookUrl']);
//                    $payment = $this->module->api->payments->create($data);
//                } else {
//                    throw $e;
//                }
//            } catch (\Mollie\Api\Exceptions\ApiException $e) {
//                if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
//                    Logger::addLog(
//                        __METHOD__.' said: '.$e->getMessage(),
//                        Mollie::CRASH
//                    );
//                }
//                if (Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)) {
//                    $this->errors[] = $this->module->lang['There was an error while processing your request: '].'<br /><em>'.$e->getMessage().'</em>';
//
//                    if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
//                        $this->setTemplate('error.tpl');
//                    } else {
//                        $this->setTemplate('module:mollie/views/templates/front/error.tpl');
//                    }
//
//                    return null;
//                } else {
//                    Tools::redirect(Context::getContext()->link->getPageLink('index', true));
//                }
//            }
//        }
        return $payment;
    }
}
