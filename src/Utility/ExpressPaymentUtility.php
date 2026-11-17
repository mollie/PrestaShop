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
     * Mollie resources hold nested addresses as stdClass objects.
     *
     * @param object|array|null $value
     */
    public static function toArray($value): array
    {
        if (null === $value) {
            return [];
        }

        $decoded = json_decode((string) json_encode($value), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The reference of the shipping option the shopper picked rides on the shipping_fee line.
     *
     * @param array|null $lines
     */
    public static function findShippingReference($lines): string
    {
        if (!is_array($lines)) {
            return '';
        }

        foreach ($lines as $line) {
            $line = (array) $line;

            if (Config::EXPRESS_SHIPPING_FEE_LINE_TYPE === ($line['type'] ?? null)) {
                return (string) ($line['reference'] ?? $line['sku'] ?? '');
            }
        }

        return '';
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
