<?php

namespace Mollie\Presenter;

use Mollie;
use Smarty_Data;

class OrderListActionBuilder
{
    const FILE_NAME = 'OrderListActionBuilder';
    /**
     * @var Mollie
     */
    private $mollie;

    public function __construct(Mollie $mollie)
    {
        $this->mollie = $mollie;
    }

    public function buildOrderPaymentResendButton(Smarty_Data $smarty, $orderId)
    {
        $smarty->assign('idOrder', $orderId);

        $smarty->assign('message',
            $this->mollie->l('You will resend email with payment link to the customer', self::FILE_NAME)
        );
        $icon = $this->mollie->display(
            $this->mollie->getLocalPath(), 'views/templates/hook/admin/order-list-save-label-icon.tpl');

        $smarty->assign('icon', $icon);

        return $this->mollie->display(
            $this->mollie->getLocalPath(), 'views/templates/hook/admin/order-list-icon-container.tpl');
    }
}