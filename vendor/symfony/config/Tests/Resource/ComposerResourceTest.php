<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Resource;

use _PhpScoper5ea00cc67502b\Composer\Autoload\ClassLoader;
use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Resource\ComposerResource;
class ComposerResourceTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testGetVendor()
    {
        $res = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Resource\ComposerResource();
        $r = new \ReflectionClass(\_PhpScoper5ea00cc67502b\Composer\Autoload\ClassLoader::class);
        $found = \false;
        foreach ($res->getVendors() as $vendor) {
            if ($vendor && 0 === \strpos($r->getFileName(), $vendor)) {
                $found = \true;
                break;
            }
        }
        $this->assertTrue($found);
    }
    public function testSerializeUnserialize()
    {
        $res = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Resource\ComposerResource();
        $ser = \unserialize(\serialize($res));
        $this->assertTrue($res->isFresh(0));
        $this->assertTrue($ser->isFresh(0));
        $this->assertEquals($res, $ser);
    }
}
