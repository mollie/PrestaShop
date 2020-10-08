<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests\Adapter;

use MolliePrefix\Predis\Connection\StreamConnection;
use MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter;
class PredisAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$redis = new \MolliePrefix\Predis\Client(['host' => \getenv('REDIS_HOST')]);
    }
    public function testCreateConnection()
    {
        $redisHost = \getenv('REDIS_HOST');
        $redis = \MolliePrefix\Symfony\Component\Cache\Adapter\RedisAdapter::createConnection('redis://' . $redisHost . '/1', ['class' => \MolliePrefix\Predis\Client::class, 'timeout' => 3]);
        $this->assertInstanceOf(\MolliePrefix\Predis\Client::class, $redis);
        $connection = $redis->getConnection();
        $this->assertInstanceOf(\MolliePrefix\Predis\Connection\StreamConnection::class, $connection);
        $params = ['scheme' => 'tcp', 'host' => $redisHost, 'path' => '', 'dbindex' => '1', 'port' => 6379, 'class' => 'MolliePrefix\\Predis\\Client', 'timeout' => 3, 'persistent' => 0, 'persistent_id' => null, 'read_timeout' => 0, 'retry_interval' => 0, 'lazy' => \false, 'database' => '1', 'password' => null];
        $this->assertSame($params, $connection->getParameters()->toArray());
    }
}
