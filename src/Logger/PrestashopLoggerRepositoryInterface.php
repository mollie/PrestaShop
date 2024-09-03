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

namespace Mollie\Logger;

use Mollie\Repository\ReadOnlyCollectionRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface PrestashopLoggerRepositoryInterface extends ReadOnlyCollectionRepositoryInterface
{
    /**
     * @param string $objectId
     * @param int $shopId
     *
     * @return int|null
     */
    public function getLogIdByObjectId(string $objectId, ?int $shopId): ?int;

    public function prune(int $daysToKeep): void;
}
