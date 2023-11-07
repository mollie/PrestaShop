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

use Mollie\Install\Install\Command\MollieCartTableInstallCommand;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_5(Mollie $module): bool
{
    /** @var MollieCartTableInstallCommand $mollieCartTableInstallCommand */
    $mollieCartTableInstallCommand = $module->getService(MollieCartTableInstallCommand::class);

    try {
        \Db::getInstance()->execute($mollieCartTableInstallCommand->getCommand());
    } catch (\Throwable $exception) {
        PrestaShopLogger::addLog("Mollie upgrade error: {$exception->getMessage()}");

        return false;
    }

    return true;
}
