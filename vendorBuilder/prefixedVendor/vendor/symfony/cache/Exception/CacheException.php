<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Cache\Exception;

use MolliePrefix\Psr\Cache\CacheException as Psr6CacheInterface;
use MolliePrefix\Psr\SimpleCache\CacheException as SimpleCacheInterface;
class CacheException extends \Exception implements \MolliePrefix\Psr\Cache\CacheException, \MolliePrefix\Psr\SimpleCache\CacheException
{
}
