<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_4_0($module)
{
    /** @var ConfigurationAdapter $configurationAdapter */
    $configurationAdapter = $module->getMollieContainer(ConfigurationAdapter::class);
    $shops = Shop::getShops();
    foreach ($shops as $shop) {
        if ((int) $configurationAdapter->get(Config::MOLLIE_SEND_ORDER_CONFIRMATION, $shop['id_shop']) !== Config::NEW_ORDER_MAIL_SEND_ON_NEVER) {
            $configurationAdapter->updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, Config::NEW_ORDER_MAIL_SEND_ON_PAID);
        }
    }

    $sql = [];

    $sql[] = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mollie_payments
        ADD `reason` VARCHAR(64);
    ';

    foreach ($sql as $query) {
        if (false == Db::getInstance()->execute($query)) {
            return false;
        }
    }

    return true;
}
