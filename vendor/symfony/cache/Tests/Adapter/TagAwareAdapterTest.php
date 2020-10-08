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

use MolliePrefix\PHPUnit\Framework\MockObject\MockObject;
use MolliePrefix\Psr\Cache\CacheItemInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter;
/**
 * @group time-sensitive
 */
class TagAwareAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    public function createCachePool($defaultLifetime = 0)
    {
        return new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter(new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter('', $defaultLifetime));
    }
    public static function tearDownAfterClass()
    {
        \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\FilesystemAdapterTest::rmdir(\sys_get_temp_dir() . '/symfony-cache');
    }
    public function testInvalidTag()
    {
        $this->expectException('MolliePrefix\\Psr\\Cache\\InvalidArgumentException');
        $pool = $this->createCachePool();
        $item = $pool->getItem('foo');
        $item->tag(':');
    }
    public function testInvalidateTags()
    {
        $pool = $this->createCachePool();
        $i0 = $pool->getItem('i0');
        $i1 = $pool->getItem('i1');
        $i2 = $pool->getItem('i2');
        $i3 = $pool->getItem('i3');
        $foo = $pool->getItem('foo');
        $pool->save($i0->tag('bar'));
        $pool->save($i1->tag('foo'));
        $pool->save($i2->tag('foo')->tag('bar'));
        $pool->save($i3->tag('foo')->tag('baz'));
        $pool->save($foo);
        $pool->invalidateTags(['bar']);
        $this->assertFalse($pool->getItem('i0')->isHit());
        $this->assertTrue($pool->getItem('i1')->isHit());
        $this->assertFalse($pool->getItem('i2')->isHit());
        $this->assertTrue($pool->getItem('i3')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());
        $pool->invalidateTags(['foo']);
        $this->assertFalse($pool->getItem('i1')->isHit());
        $this->assertFalse($pool->getItem('i3')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());
        $anotherPoolInstance = $this->createCachePool();
        $this->assertFalse($anotherPoolInstance->getItem('i1')->isHit());
        $this->assertFalse($anotherPoolInstance->getItem('i3')->isHit());
        $this->assertTrue($anotherPoolInstance->getItem('foo')->isHit());
    }
    public function testInvalidateCommits()
    {
        $pool1 = $this->createCachePool();
        $foo = $pool1->getItem('foo');
        $foo->tag('tag');
        $pool1->saveDeferred($foo->set('foo'));
        $pool1->invalidateTags(['tag']);
        $pool2 = $this->createCachePool();
        $foo = $pool2->getItem('foo');
        $this->assertTrue($foo->isHit());
    }
    public function testTagsAreCleanedOnSave()
    {
        $pool = $this->createCachePool();
        $i = $pool->getItem('k');
        $pool->save($i->tag('foo'));
        $i = $pool->getItem('k');
        $pool->save($i->tag('bar'));
        $pool->invalidateTags(['foo']);
        $this->assertTrue($pool->getItem('k')->isHit());
    }
    public function testTagsAreCleanedOnDelete()
    {
        $pool = $this->createCachePool();
        $i = $pool->getItem('k');
        $pool->save($i->tag('foo'));
        $pool->deleteItem('k');
        $pool->save($pool->getItem('k'));
        $pool->invalidateTags(['foo']);
        $this->assertTrue($pool->getItem('k')->isHit());
    }
    public function testTagItemExpiry()
    {
        $pool = $this->createCachePool(10);
        $item = $pool->getItem('foo');
        $item->tag(['baz']);
        $item->expiresAfter(100);
        $pool->save($item);
        $pool->invalidateTags(['baz']);
        $this->assertFalse($pool->getItem('foo')->isHit());
        \sleep(20);
        $this->assertFalse($pool->getItem('foo')->isHit());
    }
    public function testGetPreviousTags()
    {
        $pool = $this->createCachePool();
        $i = $pool->getItem('k');
        $pool->save($i->tag('foo'));
        $i = $pool->getItem('k');
        $this->assertSame(['foo' => 'foo'], $i->getPreviousTags());
    }
    public function testPrune()
    {
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter($this->getPruneableMock());
        $this->assertTrue($cache->prune());
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter($this->getNonPruneableMock());
        $this->assertFalse($cache->prune());
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter($this->getFailingPruneableMock());
        $this->assertFalse($cache->prune());
    }
    public function testKnownTagVersionsTtl()
    {
        $itemsPool = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter('', 10);
        $tagsPool = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface::class)->getMock();
        $pool = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter($itemsPool, $tagsPool, 10);
        $item = $pool->getItem('foo');
        $item->tag(['baz']);
        $item->expiresAfter(100);
        $tag = $this->getMockBuilder(\MolliePrefix\Psr\Cache\CacheItemInterface::class)->getMock();
        $tag->expects(self::exactly(2))->method('get')->willReturn(10);
        $tagsPool->expects(self::exactly(2))->method('getItems')->willReturn(['baz' . \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter::TAGS_PREFIX => $tag]);
        $pool->save($item);
        $this->assertTrue($pool->getItem('foo')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());
        \sleep(20);
        $this->assertTrue($pool->getItem('foo')->isHit());
        \sleep(5);
        $this->assertTrue($pool->getItem('foo')->isHit());
    }
    public function testTagEntryIsCreatedForItemWithoutTags()
    {
        $pool = $this->createCachePool();
        $itemKey = 'foo';
        $item = $pool->getItem($itemKey);
        $pool->save($item);
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter();
        $this->assertTrue($adapter->hasItem(\MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter::TAGS_PREFIX . $itemKey));
    }
    public function testHasItemReturnsFalseWhenPoolDoesNotHaveItemTags()
    {
        $pool = $this->createCachePool();
        $itemKey = 'foo';
        $item = $pool->getItem($itemKey);
        $pool->save($item);
        $anotherPool = $this->createCachePool();
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter();
        $adapter->deleteItem(\MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter::TAGS_PREFIX . $itemKey);
        //simulate item losing tags pair
        $this->assertFalse($anotherPool->hasItem($itemKey));
    }
    public function testGetItemReturnsCacheMissWhenPoolDoesNotHaveItemTags()
    {
        $pool = $this->createCachePool();
        $itemKey = 'foo';
        $item = $pool->getItem($itemKey);
        $pool->save($item);
        $anotherPool = $this->createCachePool();
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter();
        $adapter->deleteItem(\MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter::TAGS_PREFIX . $itemKey);
        //simulate item losing tags pair
        $item = $anotherPool->getItem($itemKey);
        $this->assertFalse($item->isHit());
    }
    public function testHasItemReturnsFalseWhenPoolDoesNotHaveItemAndOnlyHasTags()
    {
        $pool = $this->createCachePool();
        $itemKey = 'foo';
        $item = $pool->getItem($itemKey);
        $pool->save($item);
        $anotherPool = $this->createCachePool();
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter();
        $adapter->deleteItem($itemKey);
        //simulate losing item but keeping tags
        $this->assertFalse($anotherPool->hasItem($itemKey));
    }
    public function testInvalidateTagsWithArrayAdapter()
    {
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter(new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter());
        $item = $adapter->getItem('foo');
        $this->assertFalse($item->isHit());
        $item->tag('bar');
        $item->expiresAfter(100);
        $adapter->save($item);
        $this->assertTrue($adapter->getItem('foo')->isHit());
        $adapter->invalidateTags(['bar']);
        $this->assertFalse($adapter->getItem('foo')->isHit());
    }
    public function testGetItemReturnsCacheMissWhenPoolDoesNotHaveItemAndOnlyHasTags()
    {
        $pool = $this->createCachePool();
        $itemKey = 'foo';
        $item = $pool->getItem($itemKey);
        $pool->save($item);
        $anotherPool = $this->createCachePool();
        $adapter = new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter();
        $adapter->deleteItem($itemKey);
        //simulate losing item but keeping tags
        $item = $anotherPool->getItem($itemKey);
        $this->assertFalse($item->isHit());
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Tests\Adapter\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\true);
        return $pruneable;
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getFailingPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Tests\Adapter\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\false);
        return $pruneable;
    }
    /**
     * @return MockObject|AdapterInterface
     */
    private function getNonPruneableMock()
    {
        return $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface::class)->getMock();
    }
}
