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

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\PruneableInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Traits\FilesystemTrait;
class FilesystemAdapter extends \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Adapter\AbstractAdapter implements \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\PruneableInterface
{
    use FilesystemTrait;
    /**
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $directory
     */
    public function __construct($namespace = '', $defaultLifetime = 0, $directory = null)
    {
        parent::__construct('', $defaultLifetime);
        $this->init($namespace, $directory);
    }
}
