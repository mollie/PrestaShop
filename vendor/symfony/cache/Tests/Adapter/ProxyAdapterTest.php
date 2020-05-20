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

use _PhpScoper5ea00cc67502b\Psr\Cache\CacheItemInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ProxyAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem;
use Exception;

/**
 * @group time-sensitive
 */
class ProxyAdapterTest extends AdapterTestCase
{
    protected $skippedTests = ['testDeferredSaveWithoutCommit' => 'Assumes a shared cache which ArrayAdapter is not.', 'testSaveWithoutExpire' => 'Assumes a shared cache which ArrayAdapter is not.', 'testPrune' => 'ProxyAdapter just proxies'];
    public function createCachePool($defaultLifetime = 0)
    {
        return new ProxyAdapter(new ArrayAdapter(), '', $defaultLifetime);
    }
    public function testProxyfiedItem()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('OK bar');
        $item = new CacheItem();
        $pool = new ProxyAdapter(new TestingArrayAdapter($item));
        $proxyItem = $pool->getItem('foo');
        $this->assertNotSame($item, $proxyItem);
        $pool->save($proxyItem->set('bar'));
    }
}
class TestingArrayAdapter extends ArrayAdapter
{
    private $item;
    public function __construct(CacheItemInterface $item)
    {
        $this->item = $item;
    }
    public function getItem($key)
    {
        return $this->item;
    }
    public function save(CacheItemInterface $item)
    {
        if ($item === $this->item) {
            throw new Exception('OK ' . $item->get());
        }
    }
}
