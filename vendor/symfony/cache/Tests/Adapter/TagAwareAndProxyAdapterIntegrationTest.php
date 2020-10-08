<?php

namespace MolliePrefix\Symfony\Component\Cache\Tests\Adapter;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Psr\Cache\CacheItemPoolInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\ProxyAdapter;
use MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter;
use MolliePrefix\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;
class TagAwareAndProxyAdapterIntegrationTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIntegrationUsingProxiedAdapter(\MolliePrefix\Psr\Cache\CacheItemPoolInterface $proxiedAdapter)
    {
        $cache = new \MolliePrefix\Symfony\Component\Cache\Adapter\TagAwareAdapter(new \MolliePrefix\Symfony\Component\Cache\Adapter\ProxyAdapter($proxiedAdapter));
        $item = $cache->getItem('foo');
        $item->tag(['tag1', 'tag2']);
        $item->set('bar');
        $cache->save($item);
        $this->assertSame('bar', $cache->getItem('foo')->get());
    }
    public function dataProvider()
    {
        return [
            [new \MolliePrefix\Symfony\Component\Cache\Adapter\ArrayAdapter()],
            // also testing with a non-AdapterInterface implementation
            // because the ProxyAdapter behaves slightly different for those
            [new \MolliePrefix\Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter()],
        ];
    }
}
