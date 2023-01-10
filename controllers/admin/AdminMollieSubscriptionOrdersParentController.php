<?php

declare(strict_types=1);

use Mollie\Subscription\Controller\AbstractAdminController;

/**
 * This controller is only used to create tab in dashboard for subscription order controller
 */
class AdminMollieSubscriptionOrdersParentController extends AbstractAdminController
{
    public function init()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMollieSubscriptionOrders'));
    }
}
