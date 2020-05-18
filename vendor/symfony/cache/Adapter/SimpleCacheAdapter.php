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

use _PhpScoper5ea00cc67502b\Psr\SimpleCache\CacheInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\PruneableInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Traits\ProxyTrait;
use stdClass;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class SimpleCacheAdapter extends AbstractAdapter implements PruneableInterface
{
    /**
     * @internal
     */
    const NS_SEPARATOR = '_';
    use ProxyTrait;
    private $miss;
    public function __construct(CacheInterface $pool, $namespace = '', $defaultLifetime = 0)
    {
        parent::__construct($namespace, $defaultLifetime);
        $this->pool = $pool;
        $this->miss = new stdClass();
    }
    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids)
    {
        foreach ($this->pool->getMultiple($ids, $this->miss) as $key => $value) {
            if ($this->miss !== $value) {
                (yield $key => $value);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function doHave($id)
    {
        return $this->pool->has($id);
    }
    /**
     * {@inheritdoc}
     */
    protected function doClear($namespace)
    {
        return $this->pool->clear();
    }
    /**
     * {@inheritdoc}
     */
    protected function doDelete(array $ids)
    {
        return $this->pool->deleteMultiple($ids);
    }
    /**
     * {@inheritdoc}
     */
    protected function doSave(array $values, $lifetime)
    {
        return $this->pool->setMultiple($values, 0 === $lifetime ? null : $lifetime);
    }
}
