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

/**
 * Wallets do not agree on the country prefix: Apple Pay sends "LT-47412" where the shop format is
 * "NNNNN", and a shop format like Latvia's "C-NNNN" wants the prefix a wallet may leave out.
 */
class PostcodeUtility
{
    /**
     * @return string[] the postcode as sent, without a leading country code, and with "ISO-" in front
     */
    public static function getCandidates(string $postcode, string $countryIso): array
    {
        $postcode = strtoupper(trim($postcode));

        if ('' === $postcode) {
            return [''];
        }

        $iso = strtoupper($countryIso);
        $bare = trim((string) preg_replace('/^' . preg_quote($iso, '/') . '[\s-]*/', '', $postcode));

        return array_values(array_unique([$postcode, $bare, $iso . '-' . $bare]));
    }

    /**
     * @return string|null the first form the country's postcode format accepts
     */
    public static function findValid(\Country $country, string $postcode, string $countryIso): ?string
    {
        foreach (self::getCandidates($postcode, $countryIso) as $candidate) {
            if ($country->checkZipCode($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
