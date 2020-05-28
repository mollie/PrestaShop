<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ProxyAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\TagAwareAdapter;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;
class TagAwareAndProxyAdapterIntegrationTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIntegrationUsingProxiedAdapter(\_PhpScoper5ece82d7231e4\Psr\Cache\CacheItemPoolInterface $proxiedAdapter)
    {
        $cache = new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\TagAwareAdapter(new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ProxyAdapter($proxiedAdapter));
        $item = $cache->getItem('foo');
        $item->tag(['tag1', 'tag2']);
        $item->set('bar');
        $cache->save($item);
        $this->assertSame('bar', $cache->getItem('foo')->get());
    }
    public function dataProvider()
    {
        return [
            [new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Adapter\ArrayAdapter()],
            // also testing with a non-AdapterInterface implementation
            // because the ProxyAdapter behaves slightly different for those
            [new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter()],
        ];
    }
}
