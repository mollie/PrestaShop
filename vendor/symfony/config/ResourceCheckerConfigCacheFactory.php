<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config;

use InvalidArgumentException;
use function call_user_func;
use function gettype;
use function is_callable;
use function sprintf;

/**
 * A ConfigCacheFactory implementation that validates the
 * cache with an arbitrary set of ResourceCheckers.
 *
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class ResourceCheckerConfigCacheFactory implements ConfigCacheFactoryInterface
{
    private $resourceCheckers = [];
    /**
     * @param iterable|ResourceCheckerInterface[] $resourceCheckers
     */
    public function __construct($resourceCheckers = [])
    {
        $this->resourceCheckers = $resourceCheckers;
    }
    /**
     * {@inheritdoc}
     */
    public function cache($file, $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf('Invalid type for callback argument. Expected callable, but got "%s".', gettype($callback)));
        }
        $cache = new ResourceCheckerConfigCache($file, $this->resourceCheckers);
        if (!$cache->isFresh()) {
            call_user_func($callback, $cache);
        }
        return $cache;
    }
}
