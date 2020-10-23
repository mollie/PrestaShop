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

use MolliePrefix\Psr\Log\LoggerAwareInterface;
use MolliePrefix\Psr\SimpleCache\CacheInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
use MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\Cache\ResettableInterface;
use MolliePrefix\Symfony\Component\Cache\Traits\ArrayTrait;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ArrayCache implements \MolliePrefix\Psr\SimpleCache\CacheInterface, \MolliePrefix\Psr\Log\LoggerAwareInterface, \MolliePrefix\Symfony\Component\Cache\ResettableInterface
{
    use ArrayTrait {
        ArrayTrait::deleteItem as delete;
        ArrayTrait::hasItem as has;
    }
    private $defaultLifetime;
    /**
     * @param int  $defaultLifetime
     * @param bool $storeSerialized Disabling serialization can lead to cache corruptions when storing mutable values but increases performance otherwise
     */
    public function __construct($defaultLifetime = 0, $storeSerialized = \true)
    {
        $this->defaultLifetime = (int) $defaultLifetime;
        $this->storeSerialized = $storeSerialized;
    }
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        foreach ($this->getMultiple([$key], $default) as $v) {
            return $v;
        }
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
        foreach ($keys as $key) {
            \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
        }
        return $this->generateItems($keys, \time(), function ($k, $v, $hit) use($default) {
            return $hit ? $v : $default;
        });
    }
    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        if (!\is_array($keys) && !$keys instanceof \Traversable) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Cache keys must be array or Traversable, "%s" given.', \is_object($keys) ? \get_class($keys) : \gettype($keys)));
        }
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
        return $this->setMultiple([$key => $value], $ttl);
    }
    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!\is_array($values) && !$values instanceof \Traversable) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Cache values must be array or Traversable, "%s" given.', \is_object($values) ? \get_class($values) : \gettype($values)));
        }
        $valuesArray = [];
        foreach ($values as $key => $value) {
            \is_int($key) || \MolliePrefix\Symfony\Component\Cache\CacheItem::validateKey($key);
            $valuesArray[$key] = $value;
        }
        if (\false === ($ttl = $this->normalizeTtl($ttl))) {
            return $this->deleteMultiple(\array_keys($valuesArray));
        }
        if ($this->storeSerialized) {
            foreach ($valuesArray as $key => $value) {
                try {
                    $valuesArray[$key] = \serialize($value);
                } catch (\Exception $e) {
                    $type = \is_object($value) ? \get_class($value) : \gettype($value);
                    \MolliePrefix\Symfony\Component\Cache\CacheItem::log($this->logger, 'Failed to save key "{key}" ({type})', ['key' => $key, 'type' => $type, 'exception' => $e]);
                    return \false;
                }
            }
        }
        $expiry = 0 < $ttl ? \time() + $ttl : \PHP_INT_MAX;
        foreach ($valuesArray as $key => $value) {
            $this->values[$key] = $value;
            $this->expiries[$key] = $expiry;
        }
        return \true;
    }
    private function normalizeTtl($ttl)
    {
        if (null === $ttl) {
            return $this->defaultLifetime;
        }
        if ($ttl instanceof \DateInterval) {
            $ttl = (int) \DateTime::createFromFormat('U', 0)->add($ttl)->format('U');
        }
        if (\is_int($ttl)) {
            return 0 < $ttl ? $ttl : \false;
        }
        throw new \MolliePrefix\Symfony\Component\Cache\Exception\InvalidArgumentException(\sprintf('Expiration date must be an integer, a DateInterval or null, "%s" given.', \is_object($ttl) ? \get_class($ttl) : \gettype($ttl)));
    }
}
