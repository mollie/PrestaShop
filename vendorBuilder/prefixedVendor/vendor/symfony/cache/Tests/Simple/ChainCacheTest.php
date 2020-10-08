<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests\Simple;

use MolliePrefix\PHPUnit\Framework\MockObject\MockObject;
use MolliePrefix\Psr\SimpleCache\CacheInterface;
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\Simple\ArrayCache;
use MolliePrefix\Symfony\Component\Cache\Simple\ChainCache;
use MolliePrefix\Symfony\Component\Cache\Simple\FilesystemCache;
/**
 * @group time-sensitive
 */
class ChainCacheTest extends \MolliePrefix\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \MolliePrefix\Symfony\Component\Cache\Simple\ChainCache([new \MolliePrefix\Symfony\Component\Cache\Simple\ArrayCache($defaultLifetime), new \MolliePrefix\Symfony\Component\Cache\Simple\FilesystemCache('', $defaultLifetime)], $defaultLifetime);
    }
    public function testEmptyCachesException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('At least one cache must be specified.');
        new \MolliePrefix\Symfony\Component\Cache\Simple\ChainCache([]);
    }
    public function testInvalidCacheException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The class "stdClass" does not implement');
        new \MolliePrefix\Symfony\Component\Cache\Simple\ChainCache([new \stdClass()]);
    }
    public function testPrune()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $cache = new \MolliePrefix\Symfony\Component\Cache\Simple\ChainCache([$this->getPruneableMock(), $this->getNonPruneableMock(), $this->getPruneableMock()]);
        $this->assertTrue($cache->prune());
        $cache = new \MolliePrefix\Symfony\Component\Cache\Simple\ChainCache([$this->getPruneableMock(), $this->getFailingPruneableMock(), $this->getPruneableMock()]);
        $this->assertFalse($cache->prune());
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Tests\Simple\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\true);
        return $pruneable;
    }
    /**
     * @return MockObject|PruneableCacheInterface
     */
    private function getFailingPruneableMock()
    {
        $pruneable = $this->getMockBuilder(\MolliePrefix\Symfony\Component\Cache\Tests\Simple\PruneableCacheInterface::class)->getMock();
        $pruneable->expects($this->atLeastOnce())->method('prune')->willReturn(\false);
        return $pruneable;
    }
    /**
     * @return MockObject|CacheInterface
     */
    private function getNonPruneableMock()
    {
        return $this->getMockBuilder(\MolliePrefix\Psr\SimpleCache\CacheInterface::class)->getMock();
    }
}
interface PruneableCacheInterface extends \MolliePrefix\Symfony\Component\Cache\PruneableInterface, \MolliePrefix\Psr\SimpleCache\CacheInterface
{
}
