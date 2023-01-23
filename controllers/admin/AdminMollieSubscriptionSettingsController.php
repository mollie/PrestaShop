<?php

declare(strict_types=1);

class AdminMollieSubscriptionSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;

        $this->initOptions();
    }

    public function postProcess()
    {
        parent::postProcess();
    }

    public function initOptions()
    {
        //todo: this is just test fields, they will be changed
        $this->fields_options = [
            'settings' => [
                'title' => $this->l('TEST MODE'),
                'icon' => 'icon-settings',
                'fields' => [
                    'test' => [
                        'title' => $this->l('Test mode'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ],
                ],
            ],
        ];
    }
}
