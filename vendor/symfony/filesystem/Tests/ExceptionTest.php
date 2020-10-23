<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Filesystem\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Filesystem\Exception\FileNotFoundException;
use MolliePrefix\Symfony\Component\Filesystem\Exception\IOException;
/**
 * Test class for Filesystem.
 */
class ExceptionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetPath()
    {
        $e = new \MolliePrefix\Symfony\Component\Filesystem\Exception\IOException('', 0, null, '/foo');
        $this->assertEquals('/foo', $e->getPath(), 'The pass should be returned.');
    }
    public function testGeneratedMessage()
    {
        $e = new \MolliePrefix\Symfony\Component\Filesystem\Exception\FileNotFoundException(null, 0, null, '/foo');
        $this->assertEquals('/foo', $e->getPath());
        $this->assertEquals('File "/foo" could not be found.', $e->getMessage(), 'A message should be generated.');
    }
    public function testGeneratedMessageWithoutPath()
    {
        $e = new \MolliePrefix\Symfony\Component\Filesystem\Exception\FileNotFoundException();
        $this->assertEquals('File could not be found.', $e->getMessage(), 'A message should be generated.');
    }
    public function testCustomMessage()
    {
        $e = new \MolliePrefix\Symfony\Component\Filesystem\Exception\FileNotFoundException('bar', 0, null, '/foo');
        $this->assertEquals('bar', $e->getMessage(), 'A custom message should be possible still.');
    }
}
