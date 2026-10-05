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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExpressPaymentUtility
{
    /**
     * A payment Mollie created for an Express Component session carries the session metadata,
     * so the flag set by ExpressSessionRequestBuilder identifies it in the webhook.
     *
     * @param object|null $apiPayment Mollie payment or order resource
     */
    public static function isExpressPayment($apiPayment): bool
    {
        if (!is_object($apiPayment) || !isset($apiPayment->metadata)) {
            return false;
        }

        $metadata = $apiPayment->metadata;

        if (is_array($metadata)) {
            return !empty($metadata[Config::EXPRESS_METADATA_FLAG]);
        }

        return is_object($metadata) && !empty($metadata->{Config::EXPRESS_METADATA_FLAG});
    }
}
