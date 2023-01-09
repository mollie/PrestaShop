<?php declare(strict_types=1);

class AdminMollieSettingsController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function postProcess()
    {
        if (Tools::getValue('ajax')) {
            header('Content-Type: application/json;charset=UTF-8');

            if (!method_exists($this->module, 'displayAjax' . Tools::ucfirst(Tools::getValue('action')))) {
                exit(json_encode([
                    'success' => false,
                ]));
            }
            exit(json_encode($this->module->{'displayAjax' . Tools::ucfirst(Tools::getValue('action'))}()));
        }
        /** @var \Mollie\Repository\ModuleRepository $moduleRepository */
        $moduleRepository = $this->module->getMollieContainer(\Mollie\Repository\ModuleRepository::class);
        $moduleDatabaseVersion = $moduleRepository->getModuleDatabaseVersion($this->module->name);
        $needsUpgrade = Tools::version_compare($this->module->version, $moduleDatabaseVersion, '>');
        if ($needsUpgrade) {
            $this->context->controller->errors[] = $this->module->l('Please upgrade Mollie module');

            return;
        }

        $isShopContext = Shop::getContext() === Shop::CONTEXT_SHOP;

        if (!$isShopContext) {
            $this->context->controller->errors[] = $this->module->l('Select the shop that you want to configure');

            return;
        }

        /** @var \Mollie\Service\Content\TemplateParserInterface $templateParser */
        $templateParser = $this->module->getMollieContainer(\Mollie\Service\Content\TemplateParserInterface::class);

        $isSubmitted = (bool) Tools::isSubmit("submit{$this->module->name}");

        /* @phpstan-ignore-next-line */
        if (false === Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_AWAITING) && !$isSubmitted) {
            $this->context->controller->errors[] = $this->module->l('Select an order status for \"Status for Awaiting payments\" in the \"Advanced settings\" tab');
        }

        $errors = [];

        if (Tools::isSubmit("submit{$this->module->name}")) {
            /** @var \Mollie\Service\SettingsSaveService $saveSettingsService */
            $saveSettingsService = $this->module->getMollieContainer(\Mollie\Service\SettingsSaveService::class);
            $resultMessages = $saveSettingsService->saveSettings($errors);
            if (!empty($errors)) {
                $this->context->controller->errors = $resultMessages;
            } else {
                $this->context->controller->confirmations = $resultMessages;
            }
        }

        Media::addJsDef([
            'description_message' => addslashes($this->module->l('Enter a description')),
            'payment_api' => addslashes(Mollie\Config\Config::MOLLIE_PAYMENTS_API),
            'ajaxUrl' => addslashes($this->context->link->getAdminLink('AdminMollieAjax')),
        ]);

        /* Custom logo JS vars*/
        Media::addJsDef([
            'image_size_message' => addslashes($this->module->l('Upload an image %s%x%s1%')),
            'not_valid_file_message' => addslashes($this->module->l('Invalid file: %s%')),
        ]);

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
            $this->module->getMollieContainer(\Mollie\Builder\Content\LogoInfoBlock::class),
            $this->module->getLocalPath() . 'views/templates/admin/logo.tpl'
        );

        /** @var \Mollie\Builder\Content\UpdateMessageInfoBlock $updateMessageInfoBlock */
        $updateMessageInfoBlock = $this->module->getMollieContainer(\Mollie\Builder\Content\UpdateMessageInfoBlock::class);
        $updateMessageInfoBlockData = $updateMessageInfoBlock->setAddons(false);

        $html .= $templateParser->parseTemplate(
            $this->context->smarty,
            $updateMessageInfoBlockData,
            $this->module->getLocalPath() . 'views/templates/admin/updateMessage.tpl'
        );

        /** @var \Mollie\Builder\Content\BaseInfoBlock $baseInfoBlock */
        $baseInfoBlock = $this->module->getMollieContainer(\Mollie\Builder\Content\BaseInfoBlock::class);
        $this->context->smarty->assign($baseInfoBlock->buildParams());

        /** @var \Mollie\Builder\FormBuilder $settingsFormBuilder */
        $settingsFormBuilder = $this->module->getMollieContainer(\Mollie\Builder\FormBuilder::class);

        try {
            $html .= $settingsFormBuilder->buildSettingsForm();
        } catch (PrestaShopDatabaseException $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            $this->context->controller->errors[] = $this->module->l('The database tables are missing. Reset the module.');
        }

        $this->content .= $html;
    }

}
