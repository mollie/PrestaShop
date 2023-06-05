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

use Mollie\Subscription\Install\Installer;
use Mollie\Subscription\Install\InstallerInterface;
use PrestaShop\PrestaShop\Adapter\Module\Tab\ModuleTabRegister;
use Symfony\Component\HttpFoundation\ParameterBag;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_0(Mollie $module): bool
{
    $sql = '
    SELECT COUNT(*) > 0 AS count
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'mollie_payments" AND COLUMN_NAME = "mandate_id"
    ';

    /** only add it if it doesn't exist */
    if (!Db::getInstance()->getValue($sql)) {
        $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mollie_payments
        ADD `mandate_id` VARCHAR(64);
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    $sql = '
    SELECT COUNT(*) > 0 AS count
    FROM information_schema.columns
    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mol_payment_method" AND column_name = "min_amount";
    ';

    /** only add it if it doesn't exist */
    if (!Db::getInstance()->getValue($sql)) {
        $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD COLUMN min_amount decimal(20,6) DEFAULT 0,
        ADD COLUMN max_amount decimal(20,6) DEFAULT 0;
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    $sql = '
    DELETE t, tl
    FROM `' . _DB_PREFIX_ . 'tab` t
    JOIN `' . _DB_PREFIX_ . 'tab_lang` tl ON t.id_tab = tl.id_tab
    WHERE t.class_name IN (\'' . $module::ADMIN_MOLLIE_CONTROLLER . '\', \'' . $module::ADMIN_MOLLIE_AJAX_CONTROLLER . '\');
    ';

    try {
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    // only for 1.7.6 version
    if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
        $sql = '
        DELETE FROM `' . _DB_PREFIX_ . 'authorization_role`
        WHERE `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_CONTROLLER . '_CREATE"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_CONTROLLER . '_READ"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_CONTROLLER . '_UPDATE"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_CONTROLLER . '_DELETE"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_AJAX_CONTROLLER . '_CREATE"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_AJAX_CONTROLLER . '_READ"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_AJAX_CONTROLLER . '_UPDATE"
        OR `slug` = "ROLE_MOD_TAB_' . $module::ADMIN_MOLLIE_AJAX_CONTROLLER . '_DELETE";
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    //todo: maybe move to container
    $installer = new \Mollie\Install\Installer(
        $module,
        new \Mollie\Service\OrderStateImageService(),
        new \Mollie\Install\DatabaseTableInstaller(),
        new \Mollie\Tracker\Segment(
            new \Mollie\Adapter\Shop(),
            new \Mollie\Adapter\Language(),
            new \Mollie\Config\Env()
        ),
        new \Mollie\Adapter\ConfigurationAdapter()
    );

    $installer->installSpecificTabs();

    /** @var ModuleTabRegister $tabRegister */
    $tabRegister = $module->getService('prestashop.adapter.module.tab.register');

    $moduleAdapter = new \PrestaShop\PrestaShop\Adapter\Module\Module();
    $moduleAdapter->instance = $module;
    $moduleAdapter->disk = new ParameterBag(
        [
            'filemtype' => 0,
            'is_present' => 1,
            'is_valid' => 1,
            'version' => null,
            'path' => '',
        ]
    );

    $moduleAdapter->attributes->set('name', $module->name);

    $tabRegister->registerTabs($moduleAdapter);

    /** @var InstallerInterface $installer */
    $installer = $module->getService(InstallerInterface::class);

    return $installer->install();
}
