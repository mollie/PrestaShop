<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Tests;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Exception\ParseException;
class ParseExceptionTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testGetMessage()
    {
        $exception = new \_PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Exception\ParseException('Error message', 42, 'foo: bar', '/var/www/app/config.yml');
        $message = 'Error message in "/var/www/app/config.yml" at line 42 (near "foo: bar")';
        $this->assertEquals($message, $exception->getMessage());
    }
    public function testGetMessageWithUnicodeInFilename()
    {
        $exception = new \_PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Exception\ParseException('Error message', 42, 'foo: bar', 'äöü.yml');
        $message = 'Error message in "äöü.yml" at line 42 (near "foo: bar")';
        $this->assertEquals($message, $exception->getMessage());
    }
}
