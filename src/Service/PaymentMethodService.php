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

namespace Mollie\Service;

use _PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentMethod;
use Address;
use Cart;
use Configuration;
use Context;
use Country;
use Customer;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\MethodCountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\LocaleUtility;
use Mollie\Utility\PaymentFeeUtility;
use Mollie\Utility\TextGeneratorUtility;
use MolPaymentMethod;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use State;
use Tools;

class PaymentMethodService
{
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var PaymentMethodRepository
     */
    private $methodRepository;
    /**
     * @var MethodCountryRepository
     */
    private $countryRepository;
    /**
     * @var CartLinesService
     */
    private $cartLinesService;

    public function __construct(
        Mollie $module,
        PaymentMethodRepository $methodRepository,
        MethodCountryRepository  $countryRepository,
        CartLinesService $cartLinesService
    )
    {
        $this->module = $module;
        $this->methodRepository = $methodRepository;
        $this->countryRepository = $countryRepository;
        $this->cartLinesService = $cartLinesService;
    }

    public function savePaymentMethod($method)
    {
        $paymentId = $this->methodRepository->getPaymentMethodIdByMethodId($method['id']);
        $paymentMethod = new MolPaymentMethod();
        if ($paymentId) {
            $paymentMethod = new MolPaymentMethod($paymentId);
        }
        $paymentMethod->id_method = $method['id'];
        $paymentMethod->method_name = $method['name'];
        $paymentMethod->enabled = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_ENABLED . $method['id']);
        $paymentMethod->title = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_TITLE . $method['id']);
        $paymentMethod->method = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_API . $method['id']);
        $paymentMethod->description = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_DESCRIPTION . $method['id']);
        $paymentMethod->is_countries_applicable = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_APPLICABLE_COUNTRIES . $method['id']);
        $paymentMethod->minimal_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MINIMUM_ORDER_VALUE . $method['id']);
        $paymentMethod->max_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_ORDER_VALUE . $method['id']);
        $paymentMethod->surcharge = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_TYPE . $method['id']);
        $paymentMethod->surcharge_fixed_amount = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT . $method['id']);
        $paymentMethod->surcharge_percentage = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_PERCENTAGE . $method['id']);
        $paymentMethod->surcharge_limit = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_LIMIT . $method['id']);
        $paymentMethod->images_json = json_encode($method['image']);

        $paymentMethod->save();

        return $paymentMethod;
    }

    /**
     * Get payment methods to show on the checkout
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 3.0.0
     * @since 3.4.0 public
     *
     * @public ✓ This method is part of the public API
     */
    public function getMethodsForCheckout()
    {
        if (!Configuration::get(Config::MOLLIE_API_KEY)) {
            return [];
        }
        $context = Context::getContext();
        $iso = Tools::strtolower($context->currency->iso_code);
        $methods = $this->methodRepository->getMethodsForCheckout();
        if (empty($methods)) {
            $methods = [];
        }
        $countryCode = Tools::strtolower($context->country->iso_code);
        $unavailableMethods = [];
        foreach (Mollie\Config\Config::$defaultMethodAvailability as $methodName => $countries) {
            if (!in_array($methodName, ['klarnapaylater', 'klarnasliceit'])
                || empty($countries)
            ) {
                continue;
            }
            if (!in_array($countryCode, $countries)) {
                $unavailableMethods[] = $methodName;
            }
        }

        foreach ($methods as $index => $method) {
            $methodObj = new MolPaymentMethod($method['id_payment_method']);
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !$methodObj->enabled
                || in_array($methodObj->id_method, $unavailableMethods)
            ) {
                unset($methods[$index]);
            }
            if ($methodObj->id_method === Mollie\Config\Config::APPLEPAY) {
                if (!Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
                    unset($methods[$index]);
                } elseif (Context::getContext()->cookie->isApplePayMethod === '0') {
                    unset($methods[$index]);
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.6.0.9', '>')) {
            foreach ($methods as $index => $methodId) {
                $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
                if (!$methodObj->is_countries_applicable) {
                    if (!$this->countryRepository->checkIfMethodIsAvailableInCountry($methodObj->id_method, $country = Country::getByIso($countryCode))) {
                        unset($methods[$index]);
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * Get payment data
     *
     * @param float|string $amount
     * @param              $currency
     * @param string $method
     * @param string|null $issuer
     * @param int|Cart $cartId
     * @param string $secureKey
     * @param MolPaymentMethod $molPaymentMethod
     * @param bool $qrCode
     * @param string $orderReference
     *
     * @return array
     * @since 3.3.0 Order reference
     */
    public function getPaymentData(
        $amount,
        $currency,
        $method,
        $issuer,
        $cartId,
        $secureKey,
        MolPaymentMethod $molPaymentMethod,
        $qrCode = false,
        $orderReference = '',
        $cardToken = false
    ) {
        if (!$orderReference) {
            $this->module->currentOrderReference = $orderReference = Order::generateReference();
        }
        $description = TextGeneratorUtility::generateDescriptionFromCart($molPaymentMethod->description, $cartId, $orderReference);
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        $paymentFee = PaymentFeeUtility::getPaymentFee($molPaymentMethod, $amount);
        $totalAmount = (number_format(str_replace(',', '.', $amount), 2, '.', ''));
        $totalAmount += $paymentFee;

        $paymentData = [
            'amount' => [
                'currency' => (string)($currency ? Tools::strtoupper($currency) : 'EUR'),
                'value' => (string)number_format($totalAmount, 2, '.', ''),
            ],
            'method' => $method,
            'redirectUrl' => ($qrCode
                ? $context->link->getModuleLink(
                    'mollie',
                    'qrcode',
                    ['cart_id' => $cartId, 'done' => 1, 'rand' => time()],
                    true
                )
                : $context->link->getModuleLink(
                    'mollie',
                    'return',
                    ['cart_id' => $cartId, 'utm_nooverride' => 1, 'rand' => time()],
                    true
                )
            ),
        ];
        if ($cardToken) {
            $paymentData['cardToken'] = $cardToken;
        }
        if (!EnvironmentUtility::isLocalEnvironment()) {
            $paymentData['webhookUrl'] = $context->link->getModuleLink(
                'mollie',
                'webhook',
                [],
                true
            );
        }

        $paymentData['metadata'] = [
            'cart_id' => $cartId,
            'order_reference' => $orderReference,
            'secure_key' => Tools::encrypt($secureKey),
        ];

        // Send webshop locale
        if (($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_PAYMENTS_API
                && Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE) === Mollie\Config\Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE)
            || $molPaymentMethod->method === Mollie\Config\Config::MOLLIE_ORDERS_API
        ) {
            $locale = LocaleUtility::getWebshopLocale();
            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                $paymentData['locale'] = $locale;
            }
        }

        if ($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_PAYMENTS_API) {
            $paymentData['description'] = str_ireplace(
                ['%'],
                [$cartId],
                $description
            );
            $paymentData['issuer'] = $issuer;

            if (isset($context->cart) && Tools::getValue('method') === 'paypal') {
                if (isset($context->cart->id_customer)) {
                    $buyer = new Customer($context->cart->id_customer);
                    $paymentData['billingEmail'] = (string)$buyer->email;
                }
                if (isset($context->cart->id_address_invoice)) {
                    $billing = new Address((int)$context->cart->id_address_invoice);
                    $paymentData['billingAddress'] = [
                        'streetAndNumber' => (string)$billing->address1 . ' ' . $billing->address2,
                        'city' => (string)$billing->city,
                        'region' => (string)State::getNameById($billing->id_state),
                        'country' => (string)Country::getIsoById($billing->id_country),
                    ];
                    $paymentData['billingAddress']['postalCode'] = (string)$billing->postcode ?: '-';
                }
                if (isset($context->cart->id_address_delivery)) {
                    $shipping = new Address((int)$context->cart->id_address_delivery);
                    $paymentData['shippingAddress'] = [
                        'streetAndNumber' => (string)$shipping->address1 . ' ' . $shipping->address2,
                        'city' => (string)$shipping->city,
                        'region' => (string)State::getNameById($shipping->id_state),
                        'country' => (string)Country::getIsoById($shipping->id_country),
                    ];
                    $paymentData['shippingAddress']['postalCode'] = (string)$shipping->postcode ?: '-';
                }
            }

            switch ($method) {
                case PaymentMethod::BANKTRANSFER:
                    $paymentData['billingEmail'] = $customer->email;
                    $paymentData['locale'] = LocaleUtility::getWebshopLocale();
                    break;
                case PaymentMethod::BITCOIN:
                    $paymentData['billingEmail'] = $customer->email;
                    break;
            }
        } elseif ($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_ORDERS_API) {
            if (isset($cart->id_address_invoice)) {
                $billing = new Address((int)$cart->id_address_invoice);
                $paymentData['billingAddress'] = [
                    'givenName' => (string)$customer->firstname,
                    'familyName' => (string)$customer->lastname,
                    'email' => (string)$customer->email,
                    'streetAndNumber' => (string)$billing->address1 . ' ' . $billing->address2,
                    'city' => (string)$billing->city,
                    'region' => (string)State::getNameById($billing->id_state),
                    'country' => (string)Country::getIsoById($billing->id_country),
                ];
                $paymentData['billingAddress']['postalCode'] = (string)$billing->postcode ?: '-';
            }
            if (isset($cart->id_address_delivery)) {
                $shipping = new Address((int)$cart->id_address_delivery);
                $paymentData['shippingAddress'] = [
                    'givenName' => (string)$customer->firstname,
                    'familyName' => (string)$customer->lastname,
                    'email' => (string)$customer->email,
                    'streetAndNumber' => (string)$shipping->address1 . ' ' . $shipping->address2,
                    'city' => (string)$shipping->city,
                    'region' => (string)State::getNameById($shipping->id_state),
                    'country' => (string)Country::getIsoById($shipping->id_country),
                ];
                $paymentData['shippingAddress']['postalCode'] = (string)$shipping->postcode ?: '-';
            }
            $paymentData['orderNumber'] = $orderReference;

            $paymentData['lines'] = $this->cartLinesService->getCartLines($amount, $paymentFee, $cart);
            $paymentData['payment'] = [];
            if (!EnvironmentUtility::isLocalEnvironment()) {
                $paymentData['payment']['webhookUrl'] = $context->link->getModuleLink(
                    'mollie',
                    'webhook',
                    [],
                    true
                );
            }
            if ($issuer) {
                $paymentData['payment']['issuer'] = $issuer;
            }
        }

        return $paymentData;
    }

}