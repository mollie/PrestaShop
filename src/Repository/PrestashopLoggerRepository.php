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
use Mollie\Logger\Logger;
use Mollie\Logger\PrestashopLoggerRepositoryInterface;
use Mollie\Utility\VersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopLoggerRepository extends CollectionRepository implements PrestashopLoggerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\PrestaShopLogger::class);
    }

    /** {@inheritDoc} */
    public function getLogIdByObjectId(string $objectId, ?int $shopId): ?int
    {
        $query = new \DbQuery();

        $query
            ->select('l.id_log')
            ->from('log', 'l')
            ->where('l.object_id = "' . pSQL($objectId) . '"')
            ->orderBy('l.id_log DESC');

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $query->where('l.id_shop = ' . (int) $shopId);
        }

        $logId = \Db::getInstance()->getValue($query);

        return (int) $logId ?: null;
    }

    public function prune(int $daysToKeep): void
    {
        Collection::from(
            $this->findAllInCollection()
                ->sqlWhere('DATEDIFF(NOW(),date_add) >= ' . $daysToKeep)
                ->where('object_type', '=', Logger::LOG_OBJECT_TYPE)
        )
            ->each(function (\PrestaShopLogger $log) {
                $log->delete();
            })
            ->realize();
    }
}
