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

namespace Mollie\Tests\Integration\Repository;

use Mollie\Logger\Logger;
use Mollie\Repository\PrestashopLoggerRepository;
use Mollie\Tests\Integration\BaseTestCase;

class PrestashopLoggerRepositoryTest extends BaseTestCase
{
    public function testPruneDeletesOnlyOldMollieLogs()
    {
        $oldMollieLogId = $this->createLog(Logger::LOG_OBJECT_TYPE, 40);
        $recentMollieLogId = $this->createLog(Logger::LOG_OBJECT_TYPE, 5);
        $oldForeignLogId = $this->createLog('Order', 40);

        (new PrestashopLoggerRepository())->prune(30);

        $this->assertFalse($this->logExists($oldMollieLogId));
        $this->assertTrue($this->logExists($recentMollieLogId));
        $this->assertTrue($this->logExists($oldForeignLogId));
    }

    private function createLog(string $objectType, int $daysOld): int
    {
        $log = new \PrestaShopLogger();
        $log->severity = 1;
        $log->error_code = 0;
        $log->message = 'logger repository prune test';
        $log->object_type = $objectType;
        $log->object_id = '1';
        $log->add();

        \Db::getInstance()->update(
            'log',
            ['date_add' => date('Y-m-d H:i:s', strtotime('-' . $daysOld . ' days'))],
            'id_log = ' . (int) $log->id
        );

        return (int) $log->id;
    }

    private function logExists(int $logId): bool
    {
        return (bool) \Db::getInstance()->getValue(
            'SELECT id_log FROM `' . _DB_PREFIX_ . 'log` WHERE id_log = ' . $logId
        );
    }
}
