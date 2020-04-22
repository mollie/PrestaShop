<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\RedisCache;
abstract class AbstractRedisCacheTest extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = ['testSetTtl' => 'Testing expiration slows down the test suite', 'testSetMultipleTtl' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $redis;
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\RedisCache(self::$redis, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public static function setUpBeforeClass()
    {
        if (!\extension_loaded('redis')) {
            self::markTestSkipped('Extension redis required.');
        }
        if (!@(new \_PhpScoper5ea00cc67502b\Redis())->connect(\getenv('REDIS_HOST'))) {
            $e = \error_get_last();
            self::markTestSkipped($e['message']);
        }
    }
    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }
}
