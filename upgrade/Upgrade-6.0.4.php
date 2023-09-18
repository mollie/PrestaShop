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
use Mollie\Utility\PsVersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_4(Mollie $module): bool
{
    if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.7.0')) {
        $module->unregisterHook('actionFrontControllerAfterInit');
        $module->registerHook('actionFrontControllerInitAfter');
    }

    updateConfigurationValues604($module);
    updateOrderStatusNames604($module);

    if (!modifyExistingTables604()) {
        return false;
    }

    return true;
}

function updateConfigurationValues604(Mollie $module)
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getService(ConfigurationAdapter::class);

    if (
        !empty($configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED))
        && !empty($configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED))
        && !empty($configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS))
        && empty($configuration->get('MOLLIE_STATUS_KLARNA_AUTHORIZED'))
        && empty($configuration->get('MOLLIE_STATUS_KLARNA_SHIPPED'))
        && empty($configuration->get('MOLLIE_KLARNA_INVOICE_ON'))
    ) {
        return;
    }

    $klarnaInvoiceOn = $configuration->get('MOLLIE_KLARNA_INVOICE_ON');

    switch ($klarnaInvoiceOn) {
        case 'MOLLIE_STATUS_KLARNA_AUTHORIZED':
            $configuration->updateValue(
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS,
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED
            );
            break;
        case 'MOLLIE_STATUS_KLARNA_SHIPPED':
            $configuration->updateValue(
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS,
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED
            );
            break;
        default:
            $configuration->updateValue(
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS,
                Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT
            );
    }

    $configuration->updateValue(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED, (int) $configuration->get('MOLLIE_STATUS_KLARNA_AUTHORIZED'));
    $configuration->updateValue(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED, (int) $configuration->get('MOLLIE_STATUS_KLARNA_SHIPPED'));

    $configuration->delete('MOLLIE_STATUS_KLARNA_AUTHORIZED');
    $configuration->delete('MOLLIE_STATUS_KLARNA_SHIPPED');
    $configuration->delete('MOLLIE_KLARNA_INVOICE_ON');
}

function updateOrderStatusNames604(Mollie $module)
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getService(ConfigurationAdapter::class);

    $authorizablePaymentStatusShippedId = (int) $configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED);
    $authorizablePaymentStatusShipped = new OrderState((int) $authorizablePaymentStatusShippedId);

    if (is_array($authorizablePaymentStatusShipped->name)) {
        foreach ($authorizablePaymentStatusShipped->name as $langId => $name) {
            $authorizablePaymentStatusShipped->name[$langId] = 'Order payment shipped';
        }
    }

    $authorizablePaymentStatusShipped->save();

    $authorizablePaymentStatusAuthorizedId = (int) $configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED);
    $authorizablePaymentStatusAuthorized = new OrderState((int) $authorizablePaymentStatusAuthorizedId);

    if (is_array($authorizablePaymentStatusAuthorized->name)) {
        foreach ($authorizablePaymentStatusAuthorized->name as $langId => $name) {
            $authorizablePaymentStatusAuthorized->name[$langId] = 'Order payment authorized';
        }
    }

    $authorizablePaymentStatusAuthorized->save();
}

function modifyExistingTables604(): bool
{
    $sql = '
    SELECT COUNT(*) > 0 AS count
    FROM information_schema.columns
    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mol_recurring_order" AND column_name = "total_tax_incl";
    ';

    /** only add it if it doesn't exist */
    if (!(int) Db::getInstance()->getValue($sql)) {
        $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_recurring_order
        ADD COLUMN total_tax_incl decimal(20, 6) NOT NULL;
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Mollie upgrade error: {$e->getMessage()}");

            return false;
        }
    }

    $sql = '
        UPDATE ' . _DB_PREFIX_ . 'mol_recurring_order ro
        JOIN ' . _DB_PREFIX_ . 'orders o ON ro.id_order = o.id_order
        SET ro.total_tax_incl = o.total_paid_tax_incl;
    ';

    try {
        Db::getInstance()->execute($sql);
    } catch (Exception $e) {
        PrestaShopLogger::addLog("Mollie upgrade error: {$e->getMessage()}");

        return false;
    }

    return true;
}

