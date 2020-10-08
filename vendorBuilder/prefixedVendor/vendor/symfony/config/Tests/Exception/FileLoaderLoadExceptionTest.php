<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Exception;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException;
class FileLoaderLoadExceptionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testMessageCannotLoadResource()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', null);
        $this->assertEquals('Cannot load resource "resource".', $exception->getMessage());
    }
    public function testMessageCannotLoadResourceWithType()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', null, null, null, 'foobar');
        $this->assertEquals('Cannot load resource "resource". Make sure there is a loader supporting the "foobar" type.', $exception->getMessage());
    }
    public function testMessageCannotLoadResourceWithAnnotationType()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', null, null, null, 'annotation');
        $this->assertEquals('Cannot load resource "resource". Make sure annotations are installed and enabled.', $exception->getMessage());
    }
    public function testMessageCannotImportResourceFromSource()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', 'sourceResource');
        $this->assertEquals('Cannot import resource "resource" from "sourceResource".', $exception->getMessage());
    }
    public function testMessageCannotImportBundleResource()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('@resource', 'sourceResource');
        $this->assertEquals('Cannot import resource "@resource" from "sourceResource". ' . 'Make sure the "resource" bundle is correctly registered and loaded in the application kernel class. ' . 'If the bundle is registered, make sure the bundle path "@resource" is not empty.', $exception->getMessage());
    }
    public function testMessageHasPreviousErrorWithDotAndUnableToLoad()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', null, null, new \Exception('There was a previous error with an ending dot.'));
        $this->assertEquals('There was a previous error with an ending dot in resource (which is loaded in resource "resource").', $exception->getMessage());
    }
    public function testMessageHasPreviousErrorWithoutDotAndUnableToLoad()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('resource', null, null, new \Exception('There was a previous error with no ending dot'));
        $this->assertEquals('There was a previous error with no ending dot in resource (which is loaded in resource "resource").', $exception->getMessage());
    }
    public function testMessageHasPreviousErrorAndUnableToLoadBundle()
    {
        $exception = new \MolliePrefix\Symfony\Component\Config\Exception\FileLoaderLoadException('@resource', null, null, new \Exception('There was a previous error with an ending dot.'));
        $this->assertEquals('There was a previous error with an ending dot in @resource ' . '(which is loaded in resource "@resource"). ' . 'Make sure the "resource" bundle is correctly registered and loaded in the application kernel class. ' . 'If the bundle is registered, make sure the bundle path "@resource" is not empty.', $exception->getMessage());
    }
}
