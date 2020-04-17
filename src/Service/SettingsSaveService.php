<?php

namespace Mollie\Service;

use Configuration;
use Context;
use Exception;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethodIssuer;
use OrderState;
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

    public function __construct(
        Mollie $module,
        CountryRepository $countryRepository,
        PaymentMethodRepository $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        ApiService $apiService
    )
    {
        $this->module = $module;
        $this->countryRepository = $countryRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->apiService = $apiService;
    }

    /**
     * @param array $errors
     *
     * @return string
     * @throws Mollie\Api\Exceptions\ApiException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function saveSettings(&$errors = [])
    {
        $mollieApiKey = Tools::getValue(Config::MOLLIE_API_KEY);
        $mollieProfileId = Tools::getValue(Config::MOLLIE_PROFILE_ID);

        if (strpos($mollieApiKey, 'live') !== 0 && strpos($mollieApiKey, 'test') !== 0) {
            $errors[] = $this->module->l('The API key needs to start with test or live.');
        }

        if (Tools::getValue(Config::METHODS_CONFIG) && json_decode(Tools::getValue(Config::METHODS_CONFIG))) {
            Configuration::updateValue(
                Config::METHODS_CONFIG,
                json_encode(@json_decode(Tools::getValue(Config::METHODS_CONFIG)))
            );
        }

        if ($this->module->api->methods !== null && Configuration::get(Config::MOLLIE_API_KEY)) {
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
                $this->countryRepository->updatePaymentMethodCountries($method['id'], $countries);
            }
        }

        $molliePaymentscreenLocale = Tools::getValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        $mollieIFrameEnabled = Tools::getValue(Config::MOLLIE_IFRAME);
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

        if (empty($errors)) {
            Configuration::updateValue(Config::MOLLIE_API_KEY, $mollieApiKey);
            Configuration::updateValue(Config::MOLLIE_PROFILE_ID, $mollieProfileId);
            Configuration::updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            Configuration::updateValue(Config::MOLLIE_IFRAME, $mollieIFrameEnabled);
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
            foreach (array_keys(Config::getStatuses()) as $name) {
                $name = Tools::strtoupper($name);
                $new = (int)Tools::getValue("MOLLIE_STATUS_{$name}");
                Configuration::updateValue("MOLLIE_STATUS_{$name}", $new);
                Config::getStatuses()[Tools::strtolower($name)] = $new;

                if ($name != \Mollie\Api\Types\PaymentStatus::STATUS_OPEN) {
                    Configuration::updateValue(
                        "MOLLIE_MAIL_WHEN_{$name}",
                        Tools::getValue("MOLLIE_MAIL_WHEN_{$name}") ? true : false
                    );
                }
            }

            if ($mollieApiKey) {
                try {
                    $this->module->api->setApiKey($mollieApiKey);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    Configuration::updateValue(Config::MOLLIE_API_KEY, null);
                    return $this->module->l('Wrong API Key!');
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