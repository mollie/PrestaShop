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

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use Carrier;
use Configuration;
use Context;
use Exception;
use Mollie;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethodIssuer;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

class SettingsSaveService
{

    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var CountryRepository
     */
    private $countryRepository;
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;
    /**
     * @var ApiService
     */
    private $apiService;
    /**
     * @var MolCarrierInformationService
     */
    private $carrierInformationService;

    public function __construct(
        Mollie $module,
        CountryRepository $countryRepository,
        PaymentMethodRepository $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        ApiService $apiService,
        MolCarrierInformationService $carrierInformationService
    ) {
        $this->module = $module;
        $this->countryRepository = $countryRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->apiService = $apiService;
        $this->carrierInformationService = $carrierInformationService;
    }

    /**
     * @param array $errors
     *
     * @return string
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function saveSettings(&$errors = [])
    {
        $oldEnvironment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
        $environment = Tools::getValue(Config::MOLLIE_ENVIRONMENT);
        $mollieApiKey = Tools::getValue(Config::MOLLIE_API_KEY);
        $mollieApiKeyTest = Tools::getValue(Config::MOLLIE_API_KEY_TEST);
        $mollieProfileId = Tools::getValue(Config::MOLLIE_PROFILE_ID);

        $apiKey = (int)$environment === Config::ENVIRONMENT_LIVE ? $mollieApiKey : $mollieApiKeyTest;
        $isApiKeyIncorrect = strpos($apiKey, 'live') !== 0 && strpos($apiKey, 'test') !== 0;

        if ($isApiKeyIncorrect) {
            $errors[] = $this->module->l('The API key needs to start with test or live.');
        }

        if (Tools::getValue(Config::METHODS_CONFIG) && json_decode(Tools::getValue(Config::METHODS_CONFIG))) {
            Configuration::updateValue(
                Config::METHODS_CONFIG,
                json_encode(@json_decode(Tools::getValue(Config::METHODS_CONFIG)))
            );
        }

        if ($oldEnvironment === $environment && $this->module->api->methods !== null && $apiKey) {
            foreach ($this->apiService->getMethodsForConfig($this->module->api, $this->module->getPathUri()) as $method) {
                try {
                    $paymentMethod = $this->paymentMethodService->savePaymentMethod($method);
                } catch (Exception $e) {
                    $errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods');
                }


                if (!$this->paymentMethodRepository->deletePaymentMethodIssuersByPaymentMethodId($paymentMethod->id)) {
                    $errors[] = $this->module->l('Something went wrong. Couldn\'t delete old payment methods issuers');
                }

                if ($method['issuers']) {
                    $paymentMethodIssuer = new MolPaymentMethodIssuer();
                    $paymentMethodIssuer->issuers_json = json_encode($method['issuers']);
                    $paymentMethodIssuer->id_payment_method = $paymentMethod->id;
                    try {
                        $paymentMethodIssuer->add();
                    } catch (Exception $e) {
                        $errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods issuer');
                    }
                }

                $countries = Tools::getValue(Config::MOLLIE_METHOD_CERTAIN_COUNTRIES . $method['id']);
                $excludedCountries = Tools::getValue(
                    Config::MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES . $method['id']
                );
                $this->countryRepository->updatePaymentMethodCountries($method['id'], $countries);
                $this->countryRepository->updatePaymentMethodExcludedCountries($method['id'], $excludedCountries);
            }
        }

        $useCustomLogo = Tools::getValue(Config::MOLLIE_SHOW_CUSTOM_LOGO);
        Configuration::updateValue(
            Config::MOLLIE_SHOW_CUSTOM_LOGO,
            $useCustomLogo
        );

        $molliePaymentscreenLocale = Tools::getValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        $mollieOrderConfirmationSand = Tools::getValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION);
        $mollieNewOrderSand = Tools::getValue(Config::MOLLIE_SEND_NEW_ORDER);
        $mollieIFrameEnabled = Tools::getValue(Config::MOLLIE_IFRAME);
        $mollieSingleClickPaymentEnabled = Tools::getValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT);
        $mollieImages = Tools::getValue(Config::MOLLIE_IMAGES);
        $mollieIssuers = Tools::getValue(Config::MOLLIE_ISSUERS);
        $mollieCss = Tools::getValue(Config::MOLLIE_CSS);
        if (!isset($mollieCss)) {
            $mollieCss = '';
        }
        $mollieLogger = Tools::getValue(Config::MOLLIE_DEBUG_LOG);
        $mollieApi = Tools::getValue(Config::MOLLIE_API);
        $mollieQrEnabled = (bool)Tools::getValue(Config::MOLLIE_QRENABLED);
        $mollieMethodCountriesEnabled = (bool)Tools::getValue(Config::MOLLIE_METHOD_COUNTRIES);
        $mollieMethodCountriesDisplayEnabled = (bool)Tools::getValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
        $mollieErrors = Tools::getValue(Config::MOLLIE_DISPLAY_ERRORS);

        $mollieShipMain = Tools::getValue(Config::MOLLIE_AUTO_SHIP_MAIN);
        if (!isset($mollieErrors)) {
            $mollieErrors = false;
        } else {
            $mollieErrors = ($mollieErrors == 1);
        }

        $apiKey = (int)$environment === Config::ENVIRONMENT_LIVE ?
            $mollieApiKey : $mollieApiKeyTest;

        if ($apiKey) {
            try {
                $api = $this->apiService->setApiKey($apiKey, $this->module->version);
                if ($api === null) {
                    throw new MollieException('Failed to connect to mollie API', MollieException::API_CONNECTION_EXCEPTION);
                }
                $this->module->api = $api;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                Configuration::updateValue(Config::MOLLIE_API_KEY, null);
                return $this->module->l('Wrong API Key!');
            }
        }

        if (empty($errors)) {
            Configuration::updateValue(Config::MOLLIE_API_KEY, $mollieApiKey);
            Configuration::updateValue(Config::MOLLIE_API_KEY_TEST, $mollieApiKeyTest);
            Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, $environment);
            Configuration::updateValue(Config::MOLLIE_PROFILE_ID, $mollieProfileId);
            Configuration::updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, $mollieOrderConfirmationSand);
            Configuration::updateValue(Config::MOLLIE_SEND_NEW_ORDER, $mollieNewOrderSand);
            Configuration::updateValue(Config::MOLLIE_IFRAME, $mollieIFrameEnabled);
            Configuration::updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT, $mollieSingleClickPaymentEnabled);
            Configuration::updateValue(Config::MOLLIE_IMAGES, $mollieImages);
            Configuration::updateValue(Config::MOLLIE_ISSUERS, $mollieIssuers);
            Configuration::updateValue(Config::MOLLIE_QRENABLED, (bool)$mollieQrEnabled);
            Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES, (bool)$mollieMethodCountriesEnabled);
            Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, (bool)$mollieMethodCountriesDisplayEnabled);
            Configuration::updateValue(Config::MOLLIE_CSS, $mollieCss);
            Configuration::updateValue(Config::MOLLIE_DISPLAY_ERRORS, (int)$mollieErrors);
            Configuration::updateValue(Config::MOLLIE_DEBUG_LOG, (int)$mollieLogger);
            Configuration::updateValue(Config::MOLLIE_API, $mollieApi);
            Configuration::updateValue(
                Config::MOLLIE_AUTO_SHIP_STATUSES,
                json_encode($this->getStatusesValue(Config::MOLLIE_AUTO_SHIP_STATUSES))
            );
            Configuration::updateValue(Config::MOLLIE_AUTO_SHIP_MAIN, (bool)$mollieShipMain);
            Configuration::updateValue(
                Config::MOLLIE_TRACKING_URLS,
                json_encode(@json_decode(Tools::getValue(Config::MOLLIE_TRACKING_URLS)))
            );
            $carriers = Carrier::getCarriers(
                Context::getContext()->language->id,
                false,
                false,
                false,
                null,
                Carrier::ALL_CARRIERS
            );
            foreach ($carriers as $carrier) {
                $urlSource = Tools::getValue(Config::MOLLIE_CARRIER_URL_SOURCE . $carrier['id_carrier']);
                $customUrl = Tools::getValue(Config::MOLLIE_CARRIER_CUSTOM_URL . $carrier['id_carrier']);
                $this->carrierInformationService->saveMolCarrierInfo($carrier['id_carrier'], $urlSource, $customUrl);
            }

            foreach (array_keys(Config::getStatuses()) as $name) {
                $name = Tools::strtoupper($name);
                if (Tools::getValue("MOLLIE_STATUS_{$name}") === false) {
                    continue;
                }
                $new = (int)Tools::getValue("MOLLIE_STATUS_{$name}");
                Configuration::updateValue("MOLLIE_STATUS_{$name}", $new);
                Config::getStatuses()[Tools::strtolower($name)] = $new;

                if ($name != PaymentStatus::STATUS_OPEN) {
                    Configuration::updateValue(
                        "MOLLIE_MAIL_WHEN_{$name}",
                        Tools::getValue("MOLLIE_MAIL_WHEN_{$name}") ? true : false
                    );
                }
            }

            $resultMessage = $this->module->l('The configuration has been saved!');
        } else {
            $resultMessage = [];
            foreach ($errors as $error) {
                $resultMessage[] = $error;
            }
        }

        return $resultMessage;
    }

    /**
     * Get all status values from the form.
     *
     * @param $key string The key that is used in the HelperForm
     *
     * @return array Array with statuses
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    private function getStatusesValue($key)
    {
        $statesEnabled = [];
        $context = Context::getContext();
        foreach (OrderState::getOrderStates($context->language->id) as $state) {
            if (Tools::isSubmit($key . '_' . $state['id_order_state'])) {
                $statesEnabled[] = $state['id_order_state'];
            }
        }

        return $statesEnabled;
    }
}
