<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class ReferenceTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $ref = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo');
        $this->assertEquals('foo', (string) $ref, '__construct() sets the id of the reference, which is used for the __toString() method');
    }
}
