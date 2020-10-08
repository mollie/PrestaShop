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
use MolliePrefix\Psr\Cache\CacheItemPoolInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\ResettableInterface;
use MolliePrefix\Symfony\Component\Cache\Traits\ProxyTrait;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ProxyAdapter implements \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface, \MolliePrefix\Symfony\Component\Cache\PruneableInterface, \MolliePrefix\Symfony\Component\Cache\ResettableInterface
{
    use ProxyTrait;
    private $namespace;
    private $namespaceLen;
    private $createCacheItem;
    private $poolHash;
    private $defaultLifetime;
    /**
     * @param string $namespace
     * @param int    $defaultLifetime
     */
    public function __construct(\MolliePrefix\Psr\Cache\CacheItemPoolInterface $pool, $namespace = '', $defaultLifetime = 0)
    {
        $this->pool = $pool;
        $this->poolHash = $poolHash = \spl_object_hash($pool);
        $this->namespace = '' === $namespace ? '' : \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($namespace);
        $this->namespaceLen = \strlen($namespace);
        $this->defaultLifetime = $defaultLifetime;
        $this->createCacheItem = \Closure::bind(static function ($key, $innerItem) use($poolHash) {
            $item = new \MolliePrefix\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            $item->poolHash = $poolHash;
            if (null !== $innerItem) {
                $item->value = $innerItem->get();
                $item->isHit = $innerItem->isHit();
                $item->innerItem = $innerItem;
                $innerItem->set(null);
            }
            return $item;
        }, null, \MolliePrefix\Symfony\Component\Cache\CacheItem::class);
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $f = $this->createCacheItem;
        $item = $this->pool->getItem($this->getId($key));
        return $f($key, $item);
    }
    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if ($this->namespaceLen) {
            foreach ($keys as $i => $key) {
                $keys[$i] = $this->getId($key);
            }
        }
        return $this->generateItems($this->pool->getItems($keys));
    }
    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->pool->hasItem($this->getId($key));
    }
    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->pool->clear();
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->pool->deleteItem($this->getId($key));
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        if ($this->namespaceLen) {
            foreach ($keys as $i => $key) {
                $keys[$i] = $this->getId($key);
            }
        }
        return $this->pool->deleteItems($keys);
    }
    /**
     * {@inheritdoc}
     */
    public function save(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        return $this->doSave($item, __FUNCTION__);
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        return $this->doSave($item, __FUNCTION__);
    }
    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->pool->commit();
    }
    private function doSave(\MolliePrefix\Psr\Cache\CacheItemInterface $item, $method)
    {
        if (!$item instanceof \MolliePrefix\Symfony\Component\Cache\CacheItem) {
            return \false;
        }
        $item = (array) $item;
        $expiry = $item["\0*\0expiry"];
        if (null === $expiry && 0 < $this->defaultLifetime) {
            $expiry = \time() + $this->defaultLifetime;
        }
        if ($item["\0*\0poolHash"] === $this->poolHash && $item["\0*\0innerItem"]) {
            $innerItem = $item["\0*\0innerItem"];
        } elseif ($this->pool instanceof \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface) {
            // this is an optimization specific for AdapterInterface implementations
            // so we can save a round-trip to the backend by just creating a new item
            $f = $this->createCacheItem;
            $innerItem = $f($this->namespace . $item["\0*\0key"], null);
        } else {
            $innerItem = $this->pool->getItem($this->namespace . $item["\0*\0key"]);
        }
        $innerItem->set($item["\0*\0value"]);
        $innerItem->expiresAt(null !== $expiry ? \DateTime::createFromFormat('U', $expiry) : null);
        return $this->pool->{$method}($innerItem);
    }
    private function generateItems($items)
    {
        $f = $this->createCacheItem;
        foreach ($items as $key => $item) {
            if ($this->namespaceLen) {
                $key = \substr($key, $this->namespaceLen);
            }
            (yield $key => $f($key, $item));
        }
    }
    private function getId($key)
    {
        \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
        return $this->namespace . $key;
    }
}
