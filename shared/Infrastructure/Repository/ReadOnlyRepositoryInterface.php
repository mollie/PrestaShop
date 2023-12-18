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

namespace Mollie\Shared\Infrastructure\Repository;

use Mollie\Exception\MollieException;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ReadOnlyRepositoryInterface
{
    /**
     * @throws \PrestaShopException
     */
    public function findAll(int $langId = null): \PrestaShopCollection;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @throws \PrestaShopException
     */
    public function findOneBy(array $keyValueCriteria, int $langId = null): ?\ObjectModel;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @throws \PrestaShopException
     */
    public function findAllBy(array $keyValueCriteria, int $langId = null): ?\PrestaShopCollection;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @throws MollieException
     *
     */
    public function findOrFail(array $keyValueCriteria, int $langId = null): \ObjectModel;
}
