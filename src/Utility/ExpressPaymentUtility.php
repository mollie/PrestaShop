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
        return self::hasMetadataFlag($apiPayment, Config::EXPRESS_METADATA_FLAG);
    }

    /**
     * A collect session left the customer, addresses and shipping option to Mollie's sheet.
     *
     * @param object|null $apiPayment Mollie payment or order resource
     */
    public static function isCollectPayment($apiPayment): bool
    {
        return self::hasMetadataFlag($apiPayment, Config::EXPRESS_COLLECT_METADATA_FLAG);
    }

    /**
     * @param object|null $apiPayment
     */
    private static function hasMetadataFlag($apiPayment, string $flag): bool
    {
        if (!is_object($apiPayment) || !isset($apiPayment->metadata)) {
            return false;
        }

        $metadata = $apiPayment->metadata;

        if (is_array($metadata)) {
            return !empty($metadata[$flag]);
        }

        return is_object($metadata) && !empty($metadata->{$flag});
    }
}
