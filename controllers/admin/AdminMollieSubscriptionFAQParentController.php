<?php

declare(strict_types=1);

/**
 * This controller is only used to create tab in dashboard for subscription settings controller
 */
class AdminMollieSubscriptionFAQParentController extends ModuleAdminController
{
    public function init()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMollieSubscriptionSettings'));
    }
}
