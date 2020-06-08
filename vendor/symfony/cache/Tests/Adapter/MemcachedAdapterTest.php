<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AbstractAdapter;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter;
class MemcachedAdapterTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    protected $skippedTests = ['testHasItemReturnsFalseWhenDeferredItemIsExpired' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    protected static $client;
    public static function setUpBeforeClass()
    {
        if (!\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::isSupported()) {
            self::markTestSkipped('Extension memcached >=2.2.0 required.');
        }
        self::$client = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST'), ['binary_protocol' => \false]);
        self::$client->get('foo');
        $code = self::$client->getResultCode();
        if (\_PhpScoper5eddef0da618a\Memcached::RES_SUCCESS !== $code && \_PhpScoper5eddef0da618a\Memcached::RES_NOTFOUND !== $code) {
            self::markTestSkipped('Memcached error: ' . \strtolower(self::$client->getResultMessage()));
        }
    }
    public function createCachePool($defaultLifetime = 0)
    {
        $client = $defaultLifetime ? \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection('memcached://' . \getenv('MEMCACHED_HOST')) : self::$client;
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter($client, \str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
    public function testOptions()
    {
        $client = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([], ['libketama_compatible' => \false, 'distribution' => 'modula', 'compression' => \true, 'serializer' => 'php', 'hash' => 'md5']);
        $this->assertSame(\_PhpScoper5eddef0da618a\Memcached::SERIALIZER_PHP, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_SERIALIZER));
        $this->assertSame(\_PhpScoper5eddef0da618a\Memcached::HASH_MD5, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_HASH));
        $this->assertTrue($client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_COMPRESSION));
        $this->assertSame(0, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_LIBKETAMA_COMPATIBLE));
        $this->assertSame(\_PhpScoper5eddef0da618a\Memcached::DISTRIBUTION_MODULA, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_DISTRIBUTION));
    }
    /**
     * @dataProvider provideBadOptions
     */
    public function testBadOptions($name, $value)
    {
        if (\PHP_VERSION_ID < 80000) {
            $this->expectException('ErrorException');
            $this->expectExceptionMessage('constant(): Couldn\'t find constant Memcached::');
        } else {
            $this->expectException('Error');
            $this->expectExceptionMessage('Undefined class constant \'Memcached::');
        }
        \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([], [$name => $value]);
    }
    public function provideBadOptions()
    {
        return [['foo', 'bar'], ['hash', 'zyx'], ['serializer', 'zyx'], ['distribution', 'zyx']];
    }
    public function testDefaultOptions()
    {
        $this->assertTrue(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::isSupported());
        $client = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([]);
        $this->assertTrue($client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_COMPRESSION));
        $this->assertSame(1, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_BINARY_PROTOCOL));
        $this->assertSame(1, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_TCP_NODELAY));
        $this->assertSame(1, $client->getOption(\_PhpScoper5eddef0da618a\Memcached::OPT_LIBKETAMA_COMPATIBLE));
    }
    public function testOptionSerializer()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Cache\\Exception\\CacheException');
        $this->expectExceptionMessage('MemcachedAdapter: "serializer" option must be "php" or "igbinary".');
        if (!\_PhpScoper5eddef0da618a\Memcached::HAVE_JSON) {
            $this->markTestSkipped('Memcached::HAVE_JSON required');
        }
        new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter(\_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([], ['serializer' => 'json']));
    }
    /**
     * @dataProvider provideServersSetting
     */
    public function testServersSetting($dsn, $host, $port)
    {
        $client1 = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection($dsn);
        $client2 = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([$dsn]);
        $client3 = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection([[$host, $port]]);
        $expect = ['host' => $host, 'port' => $port];
        $f = function ($s) {
            return ['host' => $s['host'], 'port' => $s['port']];
        };
        $this->assertSame([$expect], \array_map($f, $client1->getServerList()));
        $this->assertSame([$expect], \array_map($f, $client2->getServerList()));
        $this->assertSame([$expect], \array_map($f, $client3->getServerList()));
    }
    public function provideServersSetting()
    {
        (yield ['memcached://127.0.0.1/50', '127.0.0.1', 11211]);
        (yield ['memcached://localhost:11222?weight=25', 'localhost', 11222]);
        if (\filter_var(\ini_get('memcached.use_sasl'), \FILTER_VALIDATE_BOOLEAN)) {
            (yield ['memcached://user:password@127.0.0.1?weight=50', '127.0.0.1', 11211]);
        }
        (yield ['memcached:///var/run/memcached.sock?weight=25', '/var/run/memcached.sock', 0]);
        (yield ['memcached:///var/local/run/memcached.socket?weight=25', '/var/local/run/memcached.socket', 0]);
        if (\filter_var(\ini_get('memcached.use_sasl'), \FILTER_VALIDATE_BOOLEAN)) {
            (yield ['memcached://user:password@/var/local/run/memcached.socket?weight=25', '/var/local/run/memcached.socket', 0]);
        }
    }
    /**
     * @dataProvider provideDsnWithOptions
     */
    public function testDsnWithOptions($dsn, array $options, array $expectedOptions)
    {
        $client = \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Adapter\MemcachedAdapter::createConnection($dsn, $options);
        foreach ($expectedOptions as $option => $expect) {
            $this->assertSame($expect, $client->getOption($option));
        }
    }
    public function provideDsnWithOptions()
    {
        if (!\class_exists('_PhpScoper5eddef0da618a\\Memcached')) {
            self::markTestSkipped('Extension memcached required.');
        }
        (yield ['memcached://localhost:11222?retry_timeout=10', [\_PhpScoper5eddef0da618a\Memcached::OPT_RETRY_TIMEOUT => 8], [\_PhpScoper5eddef0da618a\Memcached::OPT_RETRY_TIMEOUT => 10]]);
        (yield ['memcached://localhost:11222?socket_recv_size=1&socket_send_size=2', [\_PhpScoper5eddef0da618a\Memcached::OPT_RETRY_TIMEOUT => 8], [\_PhpScoper5eddef0da618a\Memcached::OPT_SOCKET_RECV_SIZE => 1, \_PhpScoper5eddef0da618a\Memcached::OPT_SOCKET_SEND_SIZE => 2, \_PhpScoper5eddef0da618a\Memcached::OPT_RETRY_TIMEOUT => 8]]);
    }
    public function testClear()
    {
        $this->assertTrue($this->createCachePool()->clear());
    }
}
