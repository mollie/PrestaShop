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

use DusanKasan\Knapsack\Collection;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MolLogRepository extends CollectionRepository implements MolLogRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MolLog::class);
    }

    public function prune(int $daysToKeep): void
    {
        Collection::from(
            $this->findAllInCollection()
                ->sqlWhere('DATEDIFF(NOW(),date_add) >= ' . $daysToKeep)
        )
            ->each(function (\MolLog $log) {
                $log->delete();
            })
            ->realize();
    }

    public function findAll(int $langId = null): \PrestaShopCollection
    {
        // TODO: Implement findAll() method.
    }

    public function findAllBy(array $keyValueCriteria, int $langId = null): ?\PrestaShopCollection
    {
        // TODO: Implement findAllBy() method.
    }

    public function findOrFail(array $keyValueCriteria, int $langId = null): \ObjectModel
    {
        // TODO: Implement findOrFail() method.
    }
}
