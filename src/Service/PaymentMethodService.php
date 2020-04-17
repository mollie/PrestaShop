<?php

namespace Mollie\Service;

use Configuration;
use Context;
use Country;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\MethodCountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethod;
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

    public function __construct(
        Mollie $module,
        PaymentMethodRepository $methodRepository,
        MethodCountryRepository  $countryRepository
    )
    {
        $this->module = $module;
        $this->methodRepository = $methodRepository;
        $this->countryRepository = $countryRepository;
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
        $methodIds = $this->methodRepository->getMethodIdsForCheckout();
        if (empty($methodIds)) {
            $methodIds = [];
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

        foreach ($methodIds as $index => $methodId) {
            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !$methodObj->enabled
                || in_array($methodObj->id_method, $unavailableMethods)
            ) {
                unset($methodIds[$index]);
            }
            if ($methodObj->id_method === Mollie\Config\Config::APPLEPAY) {
                if (!Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
                    unset($methodIds[$index]);
                } elseif ($_COOKIE['isApplePayMethod'] === '0') {
                    unset($methodIds[$index]);
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.6.0.9', '>')) {
            foreach ($methodIds as $index => $methodId) {
                $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
                if (!$methodObj->is_countries_applicable) {
                    if (!$this->countryRepository->checkIfMethodIsAvailableInCountry($methodObj->id_method, $country = Country::getByIso($countryCode))) {
                        unset($methodIds[$index]);
                    }
                }
            }
        }

        return $methodIds;
    }
}