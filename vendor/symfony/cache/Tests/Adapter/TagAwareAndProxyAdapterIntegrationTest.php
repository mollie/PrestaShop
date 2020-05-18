<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Adapter;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Psr\Cache\CacheItemPoolInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ArrayAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\ProxyAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\TagAwareAdapter;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;
class TagAwareAndProxyAdapterIntegrationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIntegrationUsingProxiedAdapter(CacheItemPoolInterface $proxiedAdapter)
    {
        $cache = new TagAwareAdapter(new ProxyAdapter($proxiedAdapter));
        $item = $cache->getItem('foo');
        $item->tag(['tag1', 'tag2']);
        $item->set('bar');
        $cache->save($item);
        $this->assertSame('bar', $cache->getItem('foo')->get());
    }
    public function dataProvider()
    {
        return [
            [new ArrayAdapter()],
            // also testing with a non-AdapterInterface implementation
            // because the ProxyAdapter behaves slightly different for those
            [new ExternalAdapter()],
        ];
    }
}
