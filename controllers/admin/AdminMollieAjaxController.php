<?php

use Mollie\Repository\PaymentMethodRepository;

class AdminMollieAjaxController extends ModuleAdminController
{
    public function postProcess()
    {
        $action = Tools::getValue('action');
        if ($action === 'togglePaymentMethod') {
            $paymentMethod = Tools::getValue('paymentMethod');
            $paymentStatus = Tools::getValue('status');

            /** @var PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getContainer(PaymentMethodRepository::class);
            $methodId = $paymentMethodRepo->getPaymentMethodIdByMethodId($paymentMethod);
            $method = new MolPaymentMethod($methodId);
            switch ($paymentStatus) {
                case 'deactivate':
                    $method->enabled = 0;
                    break;
                case 'activate':
                    $method->enabled = 1;
                    break;
            }
            $method->update();

            $this->ajaxDie(json_encode(
                [
                    'success' => true,
                    'paymentStatus' => $method->enabled
                ]
            ));
        }

    }
}