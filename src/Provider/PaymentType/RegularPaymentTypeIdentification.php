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

namespace Mollie\Provider\PaymentType;

use Mollie\Api\Endpoints\OrderEndpoint;

class RegularPaymentTypeIdentification implements PaymentTypeIdentificationProvider
{
    /**
     * @return string
     */
    public function getRegularPaymentIdentification()
    {
        return OrderEndpoint::RESOURCE_ID_PREFIX;
    }
}
