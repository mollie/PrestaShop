<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\PdoAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Traits\PdoPruneableTrait;
use PDO;
use function extension_loaded;
use function sleep;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @group time-sensitive
 */
class PdoAdapterTest extends AdapterTestCase
{
    use PdoPruneableTrait;
    protected static $dbFile;
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('Extension pdo_sqlite required.');
        }
        self::$dbFile = tempnam(sys_get_temp_dir(), 'sf_sqlite_cache');
        $pool = new PdoAdapter('sqlite:' . self::$dbFile);
        $pool->createTable();
    }
    public static function tearDownAfterClass()
    {
        @unlink(self::$dbFile);
    }
    public function createCachePool($defaultLifetime = 0)
    {
        return new PdoAdapter('sqlite:' . self::$dbFile, 'ns', $defaultLifetime);
    }
    public function testCleanupExpiredItems()
    {
        $pdo = new PDO('sqlite:' . self::$dbFile);
        $getCacheItemCount = function () use($pdo) {
            return (int) $pdo->query('SELECT COUNT(*) FROM cache_items')->fetch(PDO::FETCH_COLUMN);
        };
        $this->assertSame(0, $getCacheItemCount());
        $cache = $this->createCachePool();
        $item = $cache->getItem('some_nice_key');
        $item->expiresAfter(1);
        $item->set(1);
        $cache->save($item);
        $this->assertSame(1, $getCacheItemCount());
        sleep(2);
        $newItem = $cache->getItem($item->getKey());
        $this->assertFalse($newItem->isHit());
        $this->assertSame(0, $getCacheItemCount(), 'PDOAdapter must clean up expired items');
    }
}
