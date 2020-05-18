<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\ConfigCacheFactory;
use stdClass;

class ConfigCacheFactoryTest extends TestCase
{
    public function testCacheWithInvalidCallback()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid type for callback argument. Expected callable, but got "object".');
        $cacheFactory = new ConfigCacheFactory(true);
        $cacheFactory->cache('file', new stdClass());
    }
}
