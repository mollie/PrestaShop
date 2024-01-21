<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Context;
use Mollie\Adapter\API\CurlPSMollieHttpAdapter;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiKeyService
{
    /**
     * @throws ApiException
     */
    public function setApiKey(string $apiKey, string $moduleVersion, bool $subscriptionOrder = false): ?MollieApiClient
    {
        $api = new MollieApiClient(new CurlPSMollieHttpAdapter());

        $context = Context::getContext();

        if ($apiKey) {
            try {
                $api->setApiKey($apiKey);
            } catch (ApiException $e) {
                return null;
            }
        } elseif (!empty($context->employee) && Tools::getValue('Mollie_Api_Key')) {
            $api->setApiKey(Tools::getValue('Mollie_Api_Key'));
        }

        if (defined('_TB_VERSION_')) {
            $api->addVersionString('ThirtyBees/' . _TB_VERSION_);
            $api->addVersionString("MollieThirtyBees/{$moduleVersion}");

            return $api;
        }

        $api->addVersionString('PrestaShop/' . _PS_VERSION_);

        if ($subscriptionOrder) {
            $api->addVersionString("MollieSubscriptionPrestaShop/{$moduleVersion}");

            return $api;
        }

        $api->addVersionString("MolliePrestaShop/{$moduleVersion}");

        return $api;
    }
}
