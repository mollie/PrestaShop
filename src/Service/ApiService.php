<?php

namespace Mollie\Service;

use Context;
use Tools;

class ApiService
{
    public function setApiKey($apiKey, $moduleVersion)
    {
        $api = new \Mollie\Api\MollieApiClient();
        $context = Context::getContext();
        if ($apiKey) {
            try {
                $api->setApiKey($apiKey);
            } catch (\Mollie\Api\Exceptions\ApiException $e) {
                return;
            }
        } elseif (!empty($context->employee)
            && Tools::getValue('Mollie_Api_Key')
            && $context->controller instanceof AdminModulesController
        ) {
            $api->setApiKey(Tools::getValue('Mollie_Api_Key'));
        }
        if (defined('_TB_VERSION_')) {
            $api->addVersionString('ThirtyBees/' . _TB_VERSION_);
            $api->addVersionString("MollieThirtyBees/{$moduleVersion}");
        } else {
            $api->addVersionString('PrestaShop/' . _PS_VERSION_);
            $api->addVersionString("MolliePrestaShop/{$moduleVersion}");
        }

        return $api;
    }
}