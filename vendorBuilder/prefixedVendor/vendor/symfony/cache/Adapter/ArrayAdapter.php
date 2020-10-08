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
use MolliePrefix\Psr\Log\LoggerAwareInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
use MolliePrefix\Symfony\Component\Cache\ResettableInterface;
use MolliePrefix\Symfony\Component\Cache\Traits\ArrayTrait;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ArrayAdapter implements \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface, \MolliePrefix\Psr\Log\LoggerAwareInterface, \MolliePrefix\Symfony\Component\Cache\ResettableInterface
{
    use ArrayTrait;
    private $createCacheItem;
    private $defaultLifetime;
    /**
     * @param int  $defaultLifetime
     * @param bool $storeSerialized Disabling serialization can lead to cache corruptions when storing mutable values but increases performance otherwise
     */
    public function __construct($defaultLifetime = 0, $storeSerialized = \true)
    {
        $this->defaultLifetime = $defaultLifetime;
        $this->storeSerialized = $storeSerialized;
        $this->createCacheItem = \Closure::bind(static function ($key, $value, $isHit) {
            $item = new \MolliePrefix\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            $item->value = $value;
            $item->isHit = $isHit;
            return $item;
        }, null, \MolliePrefix\Symfony\Component\Cache\CacheItem::class);
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $isHit = $this->hasItem($key);
        try {
            if (!$isHit) {
                $this->values[$key] = $value = null;
            } elseif (!$this->storeSerialized) {
                $value = $this->values[$key];
            } elseif ('b:0;' === ($value = $this->values[$key])) {
                $value = \false;
            } elseif (\false === ($value = \unserialize($value))) {
                $this->values[$key] = $value = null;
                $isHit = \false;
            }
        } catch (\Exception $e) {
            \MolliePrefix\Symfony\Component\Cache\CacheItem::log($this->logger, 'Failed to unserialize key "{key}"', ['key' => $key, 'exception' => $e]);
            $this->values[$key] = $value = null;
            $isHit = \false;
        }
        $f = $this->createCacheItem;
        return $f($key, $value, $isHit);
    }
    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        foreach ($keys as $key) {
            \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
        }
        return $this->generateItems($keys, \time(), $this->createCacheItem);
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function save(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        if (!$item instanceof \MolliePrefix\Symfony\Component\Cache\CacheItem) {
            return \false;
        }
        $item = (array) $item;
        $key = $item["\0*\0key"];
        $value = $item["\0*\0value"];
        $expiry = $item["\0*\0expiry"];
        if (0 === $expiry) {
            $expiry = \PHP_INT_MAX;
        }
        if (null !== $expiry && $expiry <= \time()) {
            $this->deleteItem($key);
            return \true;
        }
        if ($this->storeSerialized) {
            try {
                $value = \serialize($value);
            } catch (\Exception $e) {
                $type = \is_object($value) ? \get_class($value) : \gettype($value);
                \MolliePrefix\Symfony\Component\Cache\CacheItem::log($this->logger, 'Failed to save key "{key}" ({type})', ['key' => $key, 'type' => $type, 'exception' => $e]);
                return \false;
            }
        }
        if (null === $expiry && 0 < $this->defaultLifetime) {
            $expiry = \time() + $this->defaultLifetime;
        }
        $this->values[$key] = $value;
        $this->expiries[$key] = null !== $expiry ? $expiry : \PHP_INT_MAX;
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        return $this->save($item);
    }
    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return \true;
    }
}
