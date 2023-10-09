<?php

declare(strict_types=1);

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Builder\Content\BaseInfoBlock;
use Mollie\Builder\Content\UpdateMessageInfoBlock;
use Mollie\Config\Config;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\ModuleRepository;
use Mollie\Service\Content\TemplateParserInterface;
use Mollie\Service\SettingsSaveService;
use PrestaShop\Module\PsEventbus\Service\PresenterService;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer as PsAccountsInstaller;

class AdminMollieSettingsController extends ModuleAdminController
{
    private const FILE_NAME = 'AdminMollieSettingsController';

    /** @var Mollie */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent(): void
    {
        $this->setEnvironmentForAccounts();
        $this->setEnvironmentForCloudSync();

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/_configure/configuration.tpl');

        $this->content .= $this->displayModuleSettings();

        $this->addJs($this->module->getPathUri() . '/views/js/admin/_configure/configuration.js');

        parent::initContent();
    }

    public function postProcess()
    {
        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->module->getService(ConfigurationAdapter::class);

        /** @var ToolsAdapter $tools */
        $tools = $this->module->getService(ToolsAdapter::class);

        $isSubmitted = $tools->isSubmit("submit{$this->module->name}");

        /* @phpstan-ignore-next-line */
        if (!$isSubmitted && !$configuration->get(Config::MOLLIE_STATUS_AWAITING)) {
            $this->context->controller->errors[] = $this->module->l('Select an order status for \"Status for Awaiting payments\" in the \"Advanced settings\" tab');
        }

        $errors = [];

        if ($tools->isSubmit("submit{$this->module->name}")) {
            /** @var SettingsSaveService $saveSettingsService */
            $saveSettingsService = $this->module->getService(SettingsSaveService::class);

            $resultMessages = $saveSettingsService->saveSettings($errors);

            if (!empty($errors)) {
                $this->context->controller->errors = array_merge(
                    $this->context->controller->errors,
                    $resultMessages
                );
            } else {
                $this->context->controller->confirmations = array_merge(
                    $this->context->controller->confirmations,
                    $resultMessages
                );
            }
        }
    }

    private function displayModuleSettings(): string
    {
        Media::addJsDef([
            'description_message' => addslashes($this->module->l('Enter a description')),
            'min_amount_message' => addslashes($this->l('You have entered incorrect min amount')),
            'max_amount_message' => addslashes($this->l('You have entered incorrect max amount')),

            'payment_api' => addslashes(Mollie\Config\Config::MOLLIE_PAYMENTS_API),
            'ajaxUrl' => addslashes($this->context->link->getAdminLink('AdminMollieAjax')),
        ]);

        /* Custom logo JS vars*/
        Media::addJsDef([
            'image_size_message' => addslashes($this->module->l('Upload an image %s%x%s1%')),
            'not_valid_file_message' => addslashes($this->module->l('Invalid file: %s%')),
        ]);

        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $this->module->getService(ModuleRepository::class);

        $moduleDatabaseVersion = $moduleRepository->getModuleDatabaseVersion($this->module->name);
        $needsUpgrade = Tools::version_compare($this->module->version, $moduleDatabaseVersion, '>');

        if ($needsUpgrade) {
            $this->context->controller->errors[] = $this->module->l('Please upgrade Mollie module');

            return '';
        }

        if (\Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            $this->context->controller->errors[] = $this->module->l('Select the shop that you want to configure');

            return '';
        }

        /** @var TemplateParserInterface $templateParser */
        $templateParser = $this->module->getService(TemplateParserInterface::class);

        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/method_countries.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/validation.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/settings.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/custom_logo.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/upgrade_notice.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/api_key_test.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/init_mollie_account.js');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/mollie.css');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/admin/logo_input.css');

        $html = $templateParser->parseTemplate(
            $this->context->smarty,
            $this->module->getService(\Mollie\Builder\Content\LogoInfoBlock::class),
            $this->module->getLocalPath() . 'views/templates/admin/logo.tpl'
        );

        /** @var UpdateMessageInfoBlock $updateMessageInfoBlock */
        $updateMessageInfoBlock = $this->module->getService(UpdateMessageInfoBlock::class);

        $updateMessageInfoBlockData = $updateMessageInfoBlock->setAddons(false);

        $html .= $templateParser->parseTemplate(
            $this->context->smarty,
            $updateMessageInfoBlockData,
            $this->module->getLocalPath() . 'views/templates/admin/updateMessage.tpl'
        );

        /** @var BaseInfoBlock $baseInfoBlock */
        $baseInfoBlock = $this->module->getService(BaseInfoBlock::class);

        $this->context->smarty->assign($baseInfoBlock->buildParams());

        /** @var \Mollie\Builder\FormBuilder $settingsFormBuilder */
        $settingsFormBuilder = $this->module->getService(\Mollie\Builder\FormBuilder::class);

        try {
            $html .= $settingsFormBuilder->buildSettingsForm();
        } catch (PrestaShopDatabaseException $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            $this->context->controller->errors[] = $this->module->l('The database tables are missing. Reset the module.');
        }

        return $html;
    }

    private function setEnvironmentForAccounts(): void
    {
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        try {
            /** @var PsAccounts $accountsFacade */
            $accountsFacade = $this->module->getService(PsAccounts::class);

            $psAccountsPresenter = $accountsFacade->getPsAccountsPresenter();
            $psAccountsService = $accountsFacade->getPsAccountsService();
        } catch (ModuleNotInstalledException $exception) {
            try {
                /** @var PsAccountsInstaller $prestashopAccountsInstaller */
                $prestashopAccountsInstaller = $this->module->getService(PsAccountsInstaller::class);

                if (!$prestashopAccountsInstaller->install()) {
                    $this->context->controller->errors[] =
                        $this->module->l('Failed to install Prestashop Accounts module. Please contact support.');

                    return;
                }
            } catch (\Throwable $exception) {
                $this->context->controller->errors[] =
                    $this->module->l('Failed to install Prestashop Accounts module. Please contact support.');

                return;
            }

            $psAccountsPresenter = $accountsFacade->getPsAccountsPresenter();
            $psAccountsService = $accountsFacade->getPsAccountsService();
        } catch (\Throwable $exception) {
            $logger->error('"PrestaShop Accounts" unknown error.', [
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
            ]);

            $this->context->controller->errors[] =
                $this->module->l('"PrestaShop Accounts" initialization failed.', self::FILE_NAME);

            return;
        }

        Media::addJsDef([
            'contextPsAccounts' => $psAccountsPresenter->present(),
        ]);

        $this->context->smarty->assign([
            'urlAccountsCdn' => $psAccountsService->getAccountsCdn(),
        ]);
    }

    private function setEnvironmentForCloudSync(): void
    {
        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        if (!$moduleManager->isInstalled('ps_eventbus')) {
            return;
        }

        /** @var \Ps_eventbus $eventbusModule */
        $eventbusModule = \Module::getInstanceByName('ps_eventbus');

        if (version_compare($eventbusModule->version, '1.9.0', '>=')) {
            /** @var PresenterService $eventbusPresenterService */
            $eventbusPresenterService = $eventbusModule->getService(PresenterService::class);

            Media::addJsDef([
                'contextPsEventbus' => $eventbusPresenterService->expose($this->module, ['orders']),
            ]);
        }

        $this->context->smarty->assign([
            'cloudSyncPathCDC' => Config::PRESTASHOP_CLOUDSYNC_CDN,
        ]);
    }
}
