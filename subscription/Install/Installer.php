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

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use PrestaShopLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Installer extends AbstractInstaller
{
    /** @var InstallerInterface */
    private $databaseInstaller;

    /** @var InstallerInterface */
    private $attributeInstaller;

    /** @var InstallerInterface */
    private $hookInstaller;

    public function __construct(
        InstallerInterface $databaseInstaller,
        InstallerInterface $attributeInstaller,
        InstallerInterface $hookInstaller
    ) {
        $this->databaseInstaller = $databaseInstaller;
        $this->attributeInstaller = $attributeInstaller;
        $this->hookInstaller = $hookInstaller;
    }

    public function install(): bool
    {
        if (!$this->databaseInstaller->install()) {
            $this->errors = $this->databaseInstaller->getErrors();
            PrestaShopLogger::addLog('Mollie subscription databases install failed', 1, null, 'Mollie', 1);

            return false;
        }

        PrestaShopLogger::addLog('Mollie subscription databases installed', 1, null, 'Mollie', 1);

        if (!$this->attributeInstaller->install()) {
            $this->errors = $this->attributeInstaller->getErrors();
            PrestaShopLogger::addLog('Mollie subscription attributes install failed', 1, null, 'Mollie', 1);

            return false;
        }

        PrestaShopLogger::addLog('Mollie subscription attributes install successful', 1, null, 'Mollie', 1);

        if (!$this->hookInstaller->install()) {
            $this->errors = $this->hookInstaller->getErrors();
            PrestaShopLogger::addLog('Mollie subscription hooks install failed', 1, null, 'Mollie', 1);

            return false;
        }

        PrestaShopLogger::addLog('Mollie subscription hooks install successful', 1, null, 'Mollie', 1);

        return true;
    }
}
