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

use _PhpScoper5ea00cc67502b\Predis\Client;
use _PhpScoper5ea00cc67502b\Predis\Connection\StreamConnection;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\RedisAdapter;
use function getenv;

class PredisAdapterTest extends AbstractRedisAdapterTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$redis = new Client(['host' => getenv('REDIS_HOST')]);
    }
    public function testCreateConnection()
    {
        $redisHost = getenv('REDIS_HOST');
        $redis = RedisAdapter::createConnection('redis://' . $redisHost . '/1', ['class' => Client::class, 'timeout' => 3]);
        $this->assertInstanceOf(Client::class, $redis);
        $connection = $redis->getConnection();
        $this->assertInstanceOf(StreamConnection::class, $connection);
        $params = ['scheme' => 'tcp', 'host' => $redisHost, 'path' => '', 'dbindex' => '1', 'port' => 6379, 'class' => '_PhpScoper5ea00cc67502b\\Predis\\Client', 'timeout' => 3, 'persistent' => 0, 'persistent_id' => null, 'read_timeout' => 0, 'retry_interval' => 0, 'lazy' => false, 'database' => '1', 'password' => null];
        $this->assertSame($params, $connection->getParameters()->toArray());
    }
}
