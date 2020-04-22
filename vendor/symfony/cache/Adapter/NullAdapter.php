<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter;

use _PhpScoper5ea00cc67502b\Psr\Cache\CacheItemInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem;
/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class NullAdapter implements \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AdapterInterface
{
    private $createCacheItem;
    public function __construct()
    {
        $this->createCacheItem = \Closure::bind(function ($key) {
            $item = new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            $item->isHit = \false;
            return $item;
        }, $this, \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem::class);
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $f = $this->createCacheItem;
        return $f($key);
    }
    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->generateItems($keys);
    }
    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function save(\_PhpScoper5ea00cc67502b\Psr\Cache\CacheItemInterface $item)
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\_PhpScoper5ea00cc67502b\Psr\Cache\CacheItemInterface $item)
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return \false;
    }
    private function generateItems(array $keys)
    {
        $f = $this->createCacheItem;
        foreach ($keys as $key) {
            (yield $key => $f($key));
        }
    }
}
