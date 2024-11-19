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

use ObjectModel;
use PrestaShopCollection;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ReadOnlyRepositoryInterface
{
    /**
     * @return PrestaShopCollection
     */
    public function findAll();

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ObjectModel|null
     */
    public function findOneBy(array $keyValueCriteria);
}
