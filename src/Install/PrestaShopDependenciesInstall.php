<?php

namespace Mollie\Install;

use Mollie\Exception\CouldNotInstallPrestaShopDependencies;
use Mollie\Factory\ModuleFactory;
use Prestashop\ModuleLibMboInstaller\Installer;
use Prestashop\ModuleLibMboInstaller\Presenter;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PsAccountsInstaller\Installer\Installer as PsAccountsInstaller;

class PrestaShopDependenciesInstall implements InstallerInterface
{
    /** @var \Mollie */
    private $module;

    public function __construct(
        ModuleFactory $moduleFactory
    ) {
        $this->module = $moduleFactory->getModule();
    }

    /**
     * @throws CouldNotInstallPrestaShopDependencies
     */
    public function install(): bool
    {
        $mboStatus = (new Presenter())->present();

        if (!$mboStatus['isInstalled']) {
            $mboInstaller = new Installer(_PS_VERSION_);

            if (!$mboInstaller->installModule()) {
                throw CouldNotInstallPrestaShopDependencies::failedToInstallMboInstaller();
            }
        }

        try {
            $this->installDependencies();
        } catch (\Throwable $exception) {
            throw CouldNotInstallPrestaShopDependencies::failedToInstallDependencies($exception);
        }

        return true;
    }

    /**
     * Install PrestaShop Integration Framework Components
     *
     * @throws \Throwable
     */
    private function installDependencies(): void
    {
        $moduleManager = ModuleManagerBuilder::getInstance();

        if (!$moduleManager) {
            throw CouldNotInstallPrestaShopDependencies::failedToRetrieveModuleManagerBuilder();
        }

        $moduleManager = $moduleManager->build();

        /** @var PsAccountsInstaller $prestashopAccountsInstaller */
        $prestashopAccountsInstaller = $this->module->getService(PsAccountsInstaller::class);

        /*
         * NOTE: install method upgrades the module if there is a newer version
         */
        if (
            $moduleManager->isInstalled('ps_accounts') &&
            !$moduleManager->isEnabled('ps_accounts')
        ) {
            $moduleManager->enable('ps_accounts');
        }

        if (!$prestashopAccountsInstaller->install()) {
            throw CouldNotInstallPrestaShopDependencies::failedToInstallPrestaShopAccounts();
        }

        /*
         * NOTE: install method upgrades the module if there is a newer version
         */
        if (
            $moduleManager->isInstalled('ps_eventbus') &&
            !$moduleManager->isEnabled('ps_eventbus')
        ) {
            $moduleManager->enable('ps_eventbus');
        }

        if (!$moduleManager->install('ps_eventbus')) {
            throw CouldNotInstallPrestaShopDependencies::failedToInstallPrestaShopEventBus();
        }
    }
}
