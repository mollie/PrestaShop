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

namespace Mollie\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The restrictions a merchant sets for a payment module in Payment > Preferences, read the way
 * PrestaShop core reads them when it lists payment options.
 */
interface ModuleRestrictionRepositoryInterface
{
    public function isCurrencyAllowed(int $moduleId, int $shopId, int $currencyId): bool;

    public function isCountryAllowed(int $moduleId, int $shopId, int $countryId): bool;

    public function isCarrierReferenceAllowed(int $moduleId, int $shopId, int $carrierReference): bool;

    /**
     * @param int[] $groupIds
     */
    public function isAnyGroupAllowed(int $moduleId, int $shopId, array $groupIds): bool;
}
