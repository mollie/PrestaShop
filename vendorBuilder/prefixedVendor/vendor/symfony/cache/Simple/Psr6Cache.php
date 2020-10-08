<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Simple;

use MolliePrefix\Psr\Cache\CacheException as Psr6CacheException;
use MolliePrefix\Psr\Cache\CacheItemPoolInterface;
use MolliePrefix\Psr\SimpleCache\CacheException as SimpleCacheException;
use MolliePrefix\Psr\SimpleCache\CacheInterface;
use MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
use MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\ResettableInterface;
use MolliePrefix\Symfony\Component\Cache\Traits\ProxyTrait;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Psr6Cache implements \MolliePrefix\Psr\SimpleCache\CacheInterface, \MolliePrefix\Symfony\Component\Cache\PruneableInterface, \MolliePrefix\Symfony\Component\Cache\ResettableInterface
{
    use ProxyTrait;
    private $createCacheItem;
    private $cacheItemPrototype;
    public function __construct(\MolliePrefix\Psr\Cache\CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
        if (!$pool instanceof \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface) {
            return;
        }
        $cacheItemPrototype =& $this->cacheItemPrototype;
        $createCacheItem = \Closure::bind(static function ($key, $value, $allowInt = \false) use(&$cacheItemPrototype) {
            $item = clone $cacheItemPrototype;
            $item->key = $allowInt && \is_int($key) ? (string) $key : \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
            $item->value = $value;
            $item->isHit = \false;
            return $item;
        }, null, \MolliePrefix\Symfony\Component\Cache\CacheItem::class);
        $this->createCacheItem = function ($key, $value, $allowInt = \false) use($createCacheItem) {
            if (null === $this->cacheItemPrototype) {
                $this->get($allowInt && \is_int($key) ? (string) $key : $key);
            }
            $this->createCacheItem = $createCacheItem;
            return $createCacheItem($key, $value, $allowInt);
        };
    }
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        try {
            $item = $this->pool->getItem($key);
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
        if (null === $this->cacheItemPrototype) {
            $this->cacheItemPrototype = clone $item;
            $this->cacheItemPrototype->set(null);
        }
        return $item->isHit() ? $item->get() : $default;
    }
    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        try {
            if (null !== ($f = $this->createCacheItem)) {
                $item = $f($key, $value);
            } else {
                $item = $this->pool->getItem($key)->set($value);
            }
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
        if (null !== $ttl) {
            $item->expiresAfter($ttl);
        }
        return $this->pool->save($item);
    }
    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        try {
            return $this->pool->deleteItem($key);
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
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
    public function getMultiple($keys, $default = null)
    {
        if ($keys instanceof \Traversable) {
            $keys = \iterator_to_array($keys, \false);
        } elseif (!\is_array($keys)) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Cache keys must be array or Traversable, "%s" given.', \is_object($keys) ? \get_class($keys) : \gettype($keys)));
        }
        try {
            $items = $this->pool->getItems($keys);
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
        $values = [];
        foreach ($items as $key => $item) {
            $values[$key] = $item->isHit() ? $item->get() : $default;
        }
        return $values;
    }
    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $valuesIsArray = \is_array($values);
        if (!$valuesIsArray && !$values instanceof \Traversable) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Cache values must be array or Traversable, "%s" given.', \is_object($values) ? \get_class($values) : \gettype($values)));
        }
        $items = [];
        try {
            if (null !== ($f = $this->createCacheItem)) {
                $valuesIsArray = \false;
                foreach ($values as $key => $value) {
                    $items[$key] = $f($key, $value, \true);
                }
            } elseif ($valuesIsArray) {
                $items = [];
                foreach ($values as $key => $value) {
                    $items[] = (string) $key;
                }
                $items = $this->pool->getItems($items);
            } else {
                foreach ($values as $key => $value) {
                    if (\is_int($key)) {
                        $key = (string) $key;
                    }
                    $items[$key] = $this->pool->getItem($key)->set($value);
                }
            }
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
        $ok = \true;
        foreach ($items as $key => $item) {
            if ($valuesIsArray) {
                $item->set($values[$key]);
            }
            if (null !== $ttl) {
                $item->expiresAfter($ttl);
            }
            $ok = $this->pool->saveDeferred($item) && $ok;
        }
        return $this->pool->commit() && $ok;
    }
    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        if ($keys instanceof \Traversable) {
            $keys = \iterator_to_array($keys, \false);
        } elseif (!\is_array($keys)) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Cache keys must be array or Traversable, "%s" given.', \is_object($keys) ? \get_class($keys) : \gettype($keys)));
        }
        try {
            return $this->pool->deleteItems($keys);
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        try {
            return $this->pool->hasItem($key);
        } catch (\MolliePrefix\Psr\SimpleCache\CacheException $e) {
            throw $e;
        } catch (\MolliePrefix\Psr\Cache\CacheException $e) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
