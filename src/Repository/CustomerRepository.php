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

    public function getExcludedCustomerGroupIds($methodId)
    {
        $sql = 'SELECT id_customer_group
                    FROM `' . _DB_PREFIX_ . 'mol_payment_method_restricted_customer_groups`
                    WHERE id_payment_method = "' . pSQL($methodId) . '"';

        $countryIds = Db::getInstance()->executeS($sql);
        $countryIdsArray = [];
        foreach ($countryIds as $countryId) {
            $countryIdsArray[] = $countryId['id_customer_group'];
        }

        return $countryIdsArray;
    }
}
