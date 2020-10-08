<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Yaml\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Yaml\Exception\ParseException;
class ParseExceptionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetMessage()
    {
        $exception = new \MolliePrefix\Symfony\Component\Yaml\Exception\ParseException('Error message', 42, 'foo: bar', '/var/www/app/config.yml');
        $message = 'Error message in "/var/www/app/config.yml" at line 42 (near "foo: bar")';
        $this->assertEquals($message, $exception->getMessage());
    }
    public function testGetMessageWithUnicodeInFilename()
    {
        $exception = new \MolliePrefix\Symfony\Component\Yaml\Exception\ParseException('Error message', 42, 'foo: bar', 'äöü.yml');
        $message = 'Error message in "äöü.yml" at line 42 (near "foo: bar")';
        $this->assertEquals($message, $exception->getMessage());
    }
}
