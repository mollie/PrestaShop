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

use MolliePrefix\Symfony\Component\Cache\Exception\CacheException;
use MolliePrefix\Symfony\Component\Cache\PruneableInterface;
use MolliePrefix\Symfony\Component\Cache\Traits\PhpFilesTrait;
class PhpFilesAdapter extends \MolliePrefix\Symfony\Component\Cache\Adapter\AbstractAdapter implements \MolliePrefix\Symfony\Component\Cache\PruneableInterface
{
    use PhpFilesTrait;
    /**
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $directory
     *
     * @throws CacheException if OPcache is not enabled
     */
    public function __construct($namespace = '', $defaultLifetime = 0, $directory = null)
    {
        if (!static::isSupported()) {
            throw new \MolliePrefix\Symfony\Component\Cache\Exception\CacheException('OPcache is not enabled.');
        }
        parent::__construct('', $defaultLifetime);
        $this->init($namespace, $directory);
        $e = new \Exception();
        $this->includeHandler = function () use($e) {
            throw $e;
        };
        $this->zendDetectUnicode = \filter_var(\ini_get('zend.detect_unicode'), \FILTER_VALIDATE_BOOLEAN);
    }
}
