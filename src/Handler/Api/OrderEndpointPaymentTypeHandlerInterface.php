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

namespace Mollie\Handler\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface OrderEndpointPaymentTypeHandlerInterface
{
    /**
     * @param string $transactionId
     *
     * @return int
     */
    public function getPaymentTypeFromTransactionId($transactionId);
}
