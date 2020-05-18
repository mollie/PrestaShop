<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\ApcuCache;
use function filter_var;
use function function_exists;
use function ini_get;
use function str_replace;
use const DIRECTORY_SEPARATOR;
use const FILTER_VALIDATE_BOOLEAN;
use const PHP_SAPI;

class ApcuCacheTest extends CacheTestCase
{
    protected $skippedTests = ['testSetTtl' => 'Testing expiration slows down the test suite', 'testSetMultipleTtl' => 'Testing expiration slows down the test suite', 'testDefaultLifeTime' => 'Testing expiration slows down the test suite'];
    public function createSimpleCache($defaultLifetime = 0)
    {
        if (!function_exists('_PhpScoper5ea00cc67502b\\apcu_fetch') || !filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) || 'cli' === PHP_SAPI && !filter_var(ini_get('apc.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('APCu extension is required.');
        }
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Fails transiently on Windows.');
        }
        return new ApcuCache(str_replace('\\', '.', __CLASS__), $defaultLifetime);
    }
}
