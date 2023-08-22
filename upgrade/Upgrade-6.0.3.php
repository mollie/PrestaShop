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

function upgrade_module_6_0_3(Mollie $module): bool
{
    updateConfigurationValues603($module);
    updateOrderStatusNames603($module);

    return true;
}

function updateConfigurationValues603(Mollie $module)
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

function updateOrderStatusNames603(Mollie $module)
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
