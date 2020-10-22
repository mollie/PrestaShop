<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Adapter;

use MolliePrefix\Psr\Cache\CacheItemInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class NullAdapter implements \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface
{
    private $createCacheItem;
    public function __construct()
    {
        $this->createCacheItem = \Closure::bind(function ($key) {
            $item = new \MolliePrefix\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            $item->isHit = \false;
            return $item;
        }, $this, \MolliePrefix\Symfony\Component\Cache\CacheItem::class);
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
    public function save(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
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
