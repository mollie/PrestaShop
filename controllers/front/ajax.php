<?php


class MollieAjaxModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        $errorMessages = explode('#', Tools::getValue('hashTag'));
        foreach ($errorMessages as $errorMessage) {
            if (strpos($errorMessage, 'message=') === 0) {
                $errorMessage = str_replace('message=', '', $errorMessage);
                $this->context->smarty->assign(array(
                    'errorMessage'   => $errorMessage

                ));
                $this->ajaxDie($this->context->smarty->fetch("{$this->module->getLocalPath()}views/templates/front/mollie_error.tpl"));
            }
        }
        $this->ajaxDie();
    }
}