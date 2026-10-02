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
