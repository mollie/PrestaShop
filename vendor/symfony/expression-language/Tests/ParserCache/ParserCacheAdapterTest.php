<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\Node;
use MolliePrefix\Symfony\Component\ExpressionLanguage\ParsedExpression;
use MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter;
/**
 * @group legacy
 */
class ParserCacheAdapterTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetItem()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $key = 'key';
        $value = 'value';
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $poolMock->expects($this->once())->method('fetch')->with($key)->willReturn($value);
        $cacheItem = $parserCacheAdapter->getItem($key);
        $this->assertEquals($value, $cacheItem->get());
        $this->assertTrue($cacheItem->isHit());
    }
    public function testSave()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $cacheItemMock = $this->getMockBuilder('MolliePrefix\\Psr\\Cache\\CacheItemInterface')->getMock();
        $key = 'key';
        $value = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParsedExpression('1 + 1', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\Node([], []));
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $poolMock->expects($this->once())->method('save')->with($key, $value);
        $cacheItemMock->expects($this->once())->method('getKey')->willReturn($key);
        $cacheItemMock->expects($this->once())->method('get')->willReturn($value);
        $parserCacheAdapter->save($cacheItemMock);
    }
    public function testGetItems()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->getItems();
    }
    public function testHasItem()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $key = 'key';
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->hasItem($key);
    }
    public function testClear()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->clear();
    }
    public function testDeleteItem()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $key = 'key';
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->deleteItem($key);
    }
    public function testDeleteItems()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $keys = ['key'];
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->deleteItems($keys);
    }
    public function testSaveDeferred()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $cacheItemMock = $this->getMockBuilder('MolliePrefix\\Psr\\Cache\\CacheItemInterface')->getMock();
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->saveDeferred($cacheItemMock);
    }
    public function testCommit()
    {
        $poolMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\ExpressionLanguage\\ParserCache\\ParserCacheInterface')->getMock();
        $parserCacheAdapter = new \MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter($poolMock);
        $this->expectException(\BadMethodCallException::class);
        $parserCacheAdapter->commit();
    }
}
