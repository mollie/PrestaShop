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

use MolliePrefix\Psr\Cache\InvalidArgumentException as Psr6CacheInterface;
use MolliePrefix\Psr\SimpleCache\InvalidArgumentException as SimpleCacheInterface;
class InvalidArgumentException extends \InvalidArgumentException implements \MolliePrefix\Psr\Cache\InvalidArgumentException, \MolliePrefix\Psr\SimpleCache\InvalidArgumentException
{
}
