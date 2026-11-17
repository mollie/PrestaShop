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
function upgrade_module_6_4_8($module)
{
    // Active shops only, as in 6.4.7: for any other shop Hook::registerHook also deletes the module's
    // module_shop row. A failure never fails the upgrade, the hook can still be set in Design > Positions.
    try {
        if (!$module->registerHook('actionCartSave', Shop::getShops(true, null, true))) {
            throw new Exception('registerHook returned false');
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not register the actionCartSave hook: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    try {
        if (!$module->registerHook('displayPersonalInformationTop', Shop::getShops(true, null, true))) {
            throw new Exception('registerHook returned false');
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not register the displayPersonalInformationTop hook: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    // Shops that upgrade keep Express on the payment step only; the cart page and the top of checkout
    // stay off until the merchant switches them on. The master switch and the buttons stay as they are.
    try {
        $placementDefaults = [
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_PAYMENT => 1,
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CART => 0,
            \Mollie\Config\Config::MOLLIE_EXPRESS_COMPONENT_PLACEMENT_CHECKOUT => 0,
        ];

        foreach ($placementDefaults as $key => $value) {
            if (false === Configuration::getGlobalValue($key)) {
                Configuration::updateGlobalValue($key, $value);
            }
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not seed the Express Component placement settings: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    // Fresh installs default the PrestaShop Account onboarding switch to off, while shops that already
    // run the module keep seeing the block. The global row is seeded so it becomes the fallback for
    // every shop without shadowing a shop-scoped value. A failure is logged and never fails the upgrade.
    try {
        if (false === Configuration::getGlobalValue(\Mollie\Config\Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED)) {
            Configuration::updateGlobalValue(\Mollie\Config\Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED, 1);
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not seed the PrestaShop Account onboarding setting: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    // Express sessions from the cart page and the top of checkout record that Mollie collects the
    // shopper details. No express session can be saved without the column, so this fails the upgrade.
    try {
        $columnExists = Db::getInstance()->executeS(
            'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'mol_express_session` LIKE \'collect_details\''
        );

        $columnAdded = !empty($columnExists) || Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'mol_express_session` ADD COLUMN `collect_details` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
        );

        if (!$columnAdded) {
            throw new Exception(Db::getInstance()->getMsgError());
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not add the collect_details column to mol_express_session: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }

    return true;
}
