<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

class Uninstaller extends AbstractUninstaller
{
    /** @var DatabaseTableUninstaller */
    private $databaseUninstaller;

    /** @var AttributeUninstaller */
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
