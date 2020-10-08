<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache;

use MolliePrefix\Psr\Cache\CacheItemInterface;
use MolliePrefix\Psr\Cache\CacheItemPoolInterface;
use MolliePrefix\Symfony\Component\Cache\CacheItem;
/**
 * @author Alexandre GESLIN <alexandre@gesl.in>
 *
 * @internal and will be removed in Symfony 4.0.
 */
class ParserCacheAdapter implements \MolliePrefix\Psr\Cache\CacheItemPoolInterface
{
    private $pool;
    private $createCacheItem;
    public function __construct(\MolliePrefix\Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface $pool)
    {
        $this->pool = $pool;
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
        $value = $this->pool->fetch($key);
        $f = $this->createCacheItem;
        return $f($key, $value, null !== $value);
    }
    /**
     * {@inheritdoc}
     */
    public function save(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        $this->pool->save($item->getKey(), $item->get());
    }
    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        throw new \BadMethodCallException('Not implemented.');
    }
}
