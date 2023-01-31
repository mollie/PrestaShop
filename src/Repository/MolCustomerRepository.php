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

use MolCustomer;

class MolCustomerRepository extends AbstractRepository
{
    public function findOneBy(array $keyValueCriteria): ?MolCustomer
    {
        /** @var ?MolCustomer $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }
}
