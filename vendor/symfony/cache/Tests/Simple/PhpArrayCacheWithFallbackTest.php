<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\FilesystemCache;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\PhpArrayCache;
use _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\FilesystemAdapterTest;
/**
 * @group time-sensitive
 */
class PhpArrayCacheWithFallbackTest extends \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Simple\CacheTestCase
{
    protected $skippedTests = [
        'testGetInvalidKeys' => 'PhpArrayCache does no validation',
        'testGetMultipleInvalidKeys' => 'PhpArrayCache does no validation',
        'testDeleteInvalidKeys' => 'PhpArrayCache does no validation',
        'testDeleteMultipleInvalidKeys' => 'PhpArrayCache does no validation',
        //'testSetValidData' => 'PhpArrayCache does no validation',
        'testSetInvalidKeys' => 'PhpArrayCache does no validation',
        'testSetInvalidTtl' => 'PhpArrayCache does no validation',
        'testSetMultipleInvalidKeys' => 'PhpArrayCache does no validation',
        'testSetMultipleInvalidTtl' => 'PhpArrayCache does no validation',
        'testHasInvalidKeys' => 'PhpArrayCache does no validation',
        'testPrune' => 'PhpArrayCache just proxies',
    ];
    protected static $file;
    public static function setUpBeforeClass()
    {
        self::$file = \sys_get_temp_dir() . '/symfony-cache/php-array-adapter-test.php';
    }
    protected function tearDown()
    {
        $this->createSimpleCache()->clear();
        if (\file_exists(\sys_get_temp_dir() . '/symfony-cache')) {
            \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Adapter\FilesystemAdapterTest::rmdir(\sys_get_temp_dir() . '/symfony-cache');
        }
    }
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\PhpArrayCache(self::$file, new \_PhpScoper5eddef0da618a\Symfony\Component\Cache\Simple\FilesystemCache('php-array-fallback', $defaultLifetime));
    }
}
