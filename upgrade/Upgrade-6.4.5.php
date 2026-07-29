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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Payment methods saved between 6.4.0 and 6.4.4 stored the raw method id as their name,
 * which ended up as the payment label on new orders. Replace those with Mollie's own
 * method names so merchants do not have to re-save every method by hand.
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_5($module)
{
    try {
        $methods = Db::getInstance()->executeS(
            'SELECT `id_payment_method`, `id_method`, `id_shop`, `live_environment`
            FROM `' . _DB_PREFIX_ . 'mol_payment_method`
            WHERE `method_name` = `id_method`'
        );

        if (empty($methods)) {
            return true;
        }

        /** @var \Mollie\Service\ApiKeyService $apiKeyService */
        $apiKeyService = $module->getService(\Mollie\Service\ApiKeyService::class);

        $namesPerEnvironment = [];

        foreach ($methods as $method) {
            $shopId = (int) $method['id_shop'];
            $environment = (int) $method['live_environment'];
            $cacheKey = $shopId . '-' . $environment;

            if (!array_key_exists($cacheKey, $namesPerEnvironment)) {
                $namesPerEnvironment[$cacheKey] = mollie_6_4_5_get_method_names(
                    $apiKeyService,
                    (string) $module->version,
                    $shopId,
                    $environment
                );
            }

            if (empty($namesPerEnvironment[$cacheKey][$method['id_method']])) {
                continue;
            }

            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'mol_payment_method`
                SET `method_name` = \'' . pSQL($namesPerEnvironment[$cacheKey][$method['id_method']]) . '\'
                WHERE `id_payment_method` = ' . (int) $method['id_payment_method']
            );
        }

        return true;
    } catch (Exception $e) {
        // The names are corrected on the next payment method save as well, so a failure
        // here must not block the upgrade.
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.5 could not refresh payment method names: ' . $e->getMessage(),
            2,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return true;
    }
}

/**
 * @return array method id => Mollie method name
 */
function mollie_6_4_5_get_method_names($apiKeyService, string $moduleVersion, int $shopId, int $environment)
{
    $apiKeyConfig = \Mollie\Config\Config::ENVIRONMENT_LIVE === $environment
        ? \Mollie\Config\Config::MOLLIE_API_KEY
        : \Mollie\Config\Config::MOLLIE_API_KEY_TEST;

    $apiKey = Configuration::get($apiKeyConfig, null, null, $shopId);

    if (!$apiKey) {
        return [];
    }

    $api = $apiKeyService->setApiKey($apiKey, $moduleVersion, false, $environment);

    if (!$api) {
        return [];
    }

    $names = [];

    foreach ($api->methods->allAvailable(['locale' => '']) as $apiMethod) {
        $names[$apiMethod->id] = $apiMethod->description;
    }

    return $names;
}
