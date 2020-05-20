<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem;
use Closure;
use Exception;
use function call_user_func;

class CacheItemTest extends TestCase
{
    public function testValidKey()
    {
        $this->assertSame('foo', CacheItem::validateKey('foo'));
    }
    /**
     * @dataProvider provideInvalidKey
     */
    public function testInvalidKey($key)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Cache key');
        CacheItem::validateKey($key);
    }
    public function provideInvalidKey()
    {
        return [[''], ['{'], ['}'], ['('], [')'], ['/'], ['\\'], ['@'], [':'], [true], [null], [1], [1.1], [[[]]], [new Exception('foo')]];
    }
    public function testTag()
    {
        $item = new CacheItem();
        $this->assertSame($item, $item->tag('foo'));
        $this->assertSame($item, $item->tag(['bar', 'baz']));
        call_user_func(Closure::bind(function () use($item) {
            $this->assertSame(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'], $item->tags);
        }, $this, CacheItem::class));
    }
    /**
     * @dataProvider provideInvalidKey
     */
    public function testInvalidTag($tag)
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\Cache\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Cache tag');
        $item = new CacheItem();
        $item->tag($tag);
    }
}
