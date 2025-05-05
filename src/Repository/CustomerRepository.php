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

use Db;
use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Customer::class);
    }

    /**
     * @param int|null $methodId
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getExcludedCustomerGroupIds(?int $methodId): array
    {
        $sql = 'SELECT id_customer_group
                    FROM `' . _DB_PREFIX_ . 'mol_excluded_customer_groups`
                    WHERE id_payment_method = "' . pSQL($methodId) . '"';

        $customerGroupsId = Db::getInstance()->executeS($sql);
        $customerIdsArray = [];
        foreach ($customerGroupsId as $customerGroupId) {
            $customerIdsArray[] = $customerGroupId['id_customer_group'];
        }

        return $customerIdsArray;
    }

    /**
     * @param int|null $idMethod
     * @param array|false $idCustomerGroups
     *
     * @return bool
     */
    public function updatePaymentMethodExcludedCustomerGroups(?int $idMethod, $idCustomerGroups)
    {
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_excluded_customer_groups WHERE `id_payment_method` = "' . $idMethod . '"';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if (!$idCustomerGroups) {
            return true;
        }

        $response = true;
        foreach ($idCustomerGroups as $idCustomerGroup) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_excluded_customer_groups` (id_payment_method, id_customer_group)
                VALUES (';

            $sql .= '"' . pSQL($idMethod) . '", ' . (int) $idCustomerGroup . ')';

            if (!Db::getInstance()->execute($sql)) {
                $response = false;
            }
        }

        return $response;
    }
}
