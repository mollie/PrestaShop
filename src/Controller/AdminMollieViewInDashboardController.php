<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Controller;

use Mollie\Utility\DashboardUrlProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Redirects the merchant to the payment/order in the Mollie dashboard.
 *
 * Kept intentionally free of the admin controller base class so Symfony can instantiate it
 * directly - it only needs to build a URL and redirect, no container or template rendering.
 */
class AdminMollieViewInDashboardController
{
    const FILE_NAME = 'AdminMollieViewInDashboardController';

    /**
     * The host is hard-coded and the transaction id is constrained by the route requirement,
     * so this only ever redirects to my.mollie.com - it is not an open redirect.
     *
     * @param string $transactionId
     *
     * @return RedirectResponse
     */
    public function viewInDashboard($transactionId)
    {
        $dashboardUrl = DashboardUrlProvider::getTransactionDashboardUrl($transactionId)
            ?: DashboardUrlProvider::DASHBOARD_BASE_URL;

        return new RedirectResponse($dashboardUrl);
    }
}
