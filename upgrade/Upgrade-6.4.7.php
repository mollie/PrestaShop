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
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_7($module)
{
    // Shops that installed before displayBackOfficeHeader joined the install hook list never got it,
    // so the manual order screen lacks the payment email checkbox. Active shops only: for any other
    // shop Hook::registerHook also deletes the module's module_shop row, disabling Mollie there.
    // A failure is logged and never fails the upgrade, the hook can still be set in Design > Positions.
    try {
        if (!$module->registerHook('displayBackOfficeHeader', Shop::getShops(true, null, true))) {
            PrestaShopLogger::addLog(
                'Mollie module upgrade to 6.4.7 could not register the displayBackOfficeHeader hook',
                3,
                null,
                'Module',
                $module->id,
                true
            );
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.7 could not register the displayBackOfficeHeader hook: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    // Express Component: session store, checkout hook and default settings. Every step is safe to
    // rerun, so a partially applied upgrade can be repeated.
    try {
        Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_express_session` (
                `id_mol_express_session` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_cart` INT(64) NOT NULL,
                `id_shop` INT(64) NOT NULL,
                `session_id` VARCHAR(64) NOT NULL,
                `amount` decimal(20,6) NOT NULL,
                `currency_iso` VARCHAR(3) NOT NULL,
                `webhook_token` VARCHAR(32) NOT NULL DEFAULT \'\',
                `status` VARCHAR(32) NOT NULL DEFAULT \'open\',
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                INDEX `idx_mol_express_session_cart` (`id_cart`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');

        if (!$module->isRegisteredInHook('displayPaymentTop')) {
            $module->registerHook('displayPaymentTop');
        }

        $expressDefaults = [
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_ENABLED => 0,
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY => 0,
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY => 0,
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL => 0,
        ];

        foreach ($expressDefaults as $key => $value) {
            if (false === Configuration::get($key)) {
                Configuration::updateValue($key, $value);
            }
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.7 could not prepare the Express Component: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }

    // Mollie replaced its Apple Pay domain association file. Shops that already serve the previous
    // Mollie file get the new one here, without having to save the Apple Pay settings again. The
    // previous file still verifies the domain, so a failed update is logged and never fails the
    // upgrade. Bypasses the service container, which is not reliably available during an upgrade.
    try {
        (new \Mollie\Handler\Certificate\ApplePayDirectCertificateHandler($module))->refresh();
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.7 could not update the Apple Pay domain association file: ' . $e->getMessage(),
            2,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    return true;
}
