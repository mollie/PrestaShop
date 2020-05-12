<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
class ReferenceTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $ref = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo');
        $this->assertEquals('foo', (string) $ref, '__construct() sets the id of the reference, which is used for the __toString() method');
    }
}
