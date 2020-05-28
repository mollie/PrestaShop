<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple;

use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Exception\CacheException;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\PruneableInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\Cache\Traits\PhpFilesTrait;
class PhpFilesCache extends \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Simple\AbstractCache implements \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\PruneableInterface
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
            throw new \_PhpScoper5ece82d7231e4\Symfony\Component\Cache\Exception\CacheException('OPcache is not enabled.');
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
