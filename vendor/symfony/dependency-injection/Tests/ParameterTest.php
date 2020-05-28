<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Parameter;
class ParameterTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $ref = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Parameter('foo');
        $this->assertEquals('foo', (string) $ref, '__construct() sets the id of the parameter, which is used for the __toString() method');
    }
}
