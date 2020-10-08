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
use MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;
/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @group time-sensitive
 */
class ChainAdapterTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Adapter\AdapterTestCase
{
    public function createCachePool($defaultLifetime = 0)
    {
        return new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter($defaultLifetime), new \MolliePrefix\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter($defaultLifetime), new \MolliePrefix\Symfony\Component\Cache\Adapter\FilesystemAdapter('', $defaultLifetime)], $defaultLifetime);
    }
    public function testEmptyAdaptersException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('At least one adapter must be specified.');
        new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([]);
    }
    public function testInvalidAdapterException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The class "stdClass" does not implement');
        new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([new \stdClass()]);
    }
    public function testPrune()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([$this->getPruneableMock(), $this->getNonPruneableMock(), $this->getPruneableMock()]);
        $this->assertTrue($cache->prune());
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([$this->getPruneableMock(), $this->getFailingPruneableMock(), $this->getPruneableMock()]);
        $this->assertFalse($cache->prune());
    }
    public function testMultipleCachesExpirationWhenCommonTtlIsNotSet()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $adapter1 = new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter(4);
        $adapter2 = new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter(2);
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([$adapter1, $adapter2]);
        $cache->save($cache->getItem('key')->set('value'));
        $item = $adapter1->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        $item = $adapter2->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        \sleep(2);
        $item = $adapter1->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        $item = $adapter2->getItem('key');
        $this->assertFalse($item->isHit());
        \sleep(2);
        $item = $adapter1->getItem('key');
        $this->assertFalse($item->isHit());
        $adapter2->save($adapter2->getItem('key1')->set('value1'));
        $item = $cache->getItem('key1');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value1', $item->get());
        \sleep(2);
        $item = $adapter1->getItem('key1');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value1', $item->get());
        $item = $adapter2->getItem('key1');
        $this->assertFalse($item->isHit());
        \sleep(2);
        $item = $adapter1->getItem('key1');
        $this->assertFalse($item->isHit());
    }
    public function testMultipleCachesExpirationWhenCommonTtlIsSet()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $adapter1 = new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter(4);
        $adapter2 = new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter(2);
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\ChainAdapter([$adapter1, $adapter2], 6);
        $cache->save($cache->getItem('key')->set('value'));
        $item = $adapter1->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        $item = $adapter2->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        \sleep(2);
        $item = $adapter1->getItem('key');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value', $item->get());
        $item = $adapter2->getItem('key');
        $this->assertFalse($item->isHit());
        \sleep(2);
        $item = $adapter1->getItem('key');
        $this->assertFalse($item->isHit());
        $adapter2->save($adapter2->getItem('key1')->set('value1'));
        $item = $cache->getItem('key1');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value1', $item->get());
        \sleep(2);
        $item = $adapter1->getItem('key1');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value1', $item->get());
        $item = $adapter2->getItem('key1');
        $this->assertFalse($item->isHit());
        \sleep(2);
        $item = $adapter1->getItem('key1');
        $this->assertTrue($item->isHit());
        $this->assertEquals('value1', $item->get());
        \sleep(2);
        $item = $adapter1->getItem('key1');
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
interface PruneableCacheInterface extends \MolliePrefix\Symfony\Component\Cache\PruneableInterface, \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface
{
}
