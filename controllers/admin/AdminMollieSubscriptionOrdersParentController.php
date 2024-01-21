<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

declare(strict_types=1);

use Mollie\Subscription\Controller\AbstractAdminController;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
