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

namespace Mollie\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardUrlProvider
{
    const DASHBOARD_BASE_URL = 'https://my.mollie.com/dashboard';

    /**
     * Builds a link to the payment/order in the Mollie dashboard from its transaction id.
     *
     * Orders API transactions (ord_...) live under /orders, everything else (tr_.../pay_...)
     * under /payments. Returns null when there is no transaction id to link to.
     *
     * @param string|null $transactionId
     *
     * @return string|null
     */
    public static function getTransactionDashboardUrl($transactionId)
    {
        if (empty($transactionId)) {
            return null;
        }

        $segment = TransactionUtility::isOrderTransaction($transactionId) ? 'orders' : 'payments';

        return self::DASHBOARD_BASE_URL . '/' . $segment . '/' . $transactionId;
    }
}
