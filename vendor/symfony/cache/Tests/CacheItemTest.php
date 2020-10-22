<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
class CacheItemTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testValidKey()
    {
        $this->assertSame('foo', \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey('foo'));
    }
    /**
     * @dataProvider provideInvalidKey
     */
    public function testInvalidKey($key)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Cache key');
        \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
    }
    public function provideInvalidKey()
    {
        return [[''], ['{'], ['}'], ['('], [')'], ['/'], ['\\'], ['@'], [':'], [\true], [null], [1], [1.1], [[[]]], [new \Exception('foo')]];
    }
    public function testTag()
    {
        $item = new \MolliePrefix\Symfony\Component\Cache\CacheItem();
        $this->assertSame($item, $item->tag('foo'));
        $this->assertSame($item, $item->tag(['bar', 'baz']));
        \call_user_func(\Closure::bind(function () use($item) {
            $this->assertSame(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'], $item->tags);
        }, $this, \MolliePrefix\Symfony\Component\Cache\CacheItem::class));
    }
    /**
     * @dataProvider provideInvalidKey
     */
    public function testInvalidTag($tag)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Cache tag');
        $item = new \MolliePrefix\Symfony\Component\Cache\CacheItem();
        $item->tag($tag);
    }
}
