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
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\ResettableInterface;
/**
 * An adapter that collects data about all cache calls.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class TraceableAdapter implements \MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface, \MolliePrefix\Symfony\Component\Cache\PruneableInterface, \MolliePrefix\Symfony\Component\Cache\ResettableInterface
{
    protected $pool;
    private $calls = [];
    public function __construct(\MolliePrefix\Symfony\Component\Cache\Adapter\AdapterInterface $pool)
    {
        $this->pool = $pool;
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $event = $this->start(__FUNCTION__);
        try {
            $item = $this->pool->getItem($key);
        } finally {
            $event->end = \microtime(\true);
        }
        if ($event->result[$key] = $item->isHit()) {
            ++$event->hits;
        } else {
            ++$event->misses;
        }
        return $item;
    }
    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result[$key] = $this->pool->hasItem($key);
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result[$key] = $this->pool->deleteItem($key);
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function save(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result[$item->getKey()] = $this->pool->save($item);
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function saveDeferred(\MolliePrefix\Psr\Cache\CacheItemInterface $item)
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result[$item->getKey()] = $this->pool->saveDeferred($item);
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $event = $this->start(__FUNCTION__);
        try {
            $result = $this->pool->getItems($keys);
        } finally {
            $event->end = \microtime(\true);
        }
        $f = function () use($result, $event) {
            $event->result = [];
            foreach ($result as $key => $item) {
                if ($event->result[$key] = $item->isHit()) {
                    ++$event->hits;
                } else {
                    ++$event->misses;
                }
                (yield $key => $item);
            }
        };
        return $f();
    }
    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result = $this->pool->clear();
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $event = $this->start(__FUNCTION__);
        $event->result['keys'] = $keys;
        try {
            return $event->result['result'] = $this->pool->deleteItems($keys);
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result = $this->pool->commit();
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function prune()
    {
        if (!$this->pool instanceof \MolliePrefix\Symfony\Component\Cache\PruneableInterface) {
            return \false;
        }
        $event = $this->start(__FUNCTION__);
        try {
            return $event->result = $this->pool->prune();
        } finally {
            $event->end = \microtime(\true);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        if ($this->pool instanceof \MolliePrefix\Symfony\Component\Cache\ResettableInterface) {
            $this->pool->reset();
        }
        $this->clearCalls();
    }
    public function getCalls()
    {
        return $this->calls;
    }
    public function clearCalls()
    {
        $this->calls = [];
    }
    protected function start($name)
    {
        $this->calls[] = $event = new \MolliePrefix\Symfony\Component\Cache\Adapter\TraceableAdapterEvent();
        $event->name = $name;
        $event->start = \microtime(\true);
        return $event;
    }
}
class TraceableAdapterEvent
{
    public $name;
    public $start;
    public $end;
    public $result;
    public $hits = 0;
    public $misses = 0;
}
