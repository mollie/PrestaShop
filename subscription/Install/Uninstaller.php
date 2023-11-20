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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Uninstaller extends AbstractUninstaller
{
    /** @var UninstallerInterface */
    private $databaseUninstaller;

    /** @var UninstallerInterface */
    private $attributeUninstaller;

    public function __construct(
        UninstallerInterface $databaseUninstaller,
        UninstallerInterface $attributeUninstaller
    ) {
        $this->databaseUninstaller = $databaseUninstaller;
        $this->attributeUninstaller = $attributeUninstaller;
    }

    public function uninstall(): bool
    {
        if (!$this->databaseUninstaller->uninstall()) {
            $this->errors = $this->databaseUninstaller->getErrors();

            return false;
        }

        if (!$this->attributeUninstaller->uninstall()) {
            $this->errors = $this->attributeUninstaller->getErrors();

            return false;
        }

        return true;
    }
}
