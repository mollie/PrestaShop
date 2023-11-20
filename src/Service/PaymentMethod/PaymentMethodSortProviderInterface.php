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

namespace Mollie\Service\PaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Payment methods are being retrieved both from api and the ones stored in database. The ones that are stored
 * can be dragged in admin so this service can be used to call anywhere and sort payment options accordingly.
 */
interface PaymentMethodSortProviderInterface
{
    /**
     * @return array
     */
    public function getSortedInAscendingWayForCheckout(array $paymentMethods);

    /**
     * @return array
     */
    public function getSortedInAscendingWayForConfiguration(array $paymentMethods);
}
