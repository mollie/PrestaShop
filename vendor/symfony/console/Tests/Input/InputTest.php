<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Input;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Input\ArrayInput;
use MolliePrefix\Symfony\Component\Console\Input\InputArgument;
use MolliePrefix\Symfony\Component\Console\Input\InputDefinition;
use MolliePrefix\Symfony\Component\Console\Input\InputOption;
class InputTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name')]));
        $this->assertEquals('foo', $input->getArgument('name'), '->__construct() takes a InputDefinition as an argument');
    }
    public function testOptions()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name')]));
        $this->assertEquals('foo', $input->getOption('name'), '->getOption() returns the value for the given option');
        $input->setOption('name', 'bar');
        $this->assertEquals('bar', $input->getOption('name'), '->setOption() sets the value for a given option');
        $this->assertEquals(['name' => 'bar'], $input->getOptions(), '->getOptions() returns all option values');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('bar', '', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertEquals('default', $input->getOption('bar'), '->getOption() returns the default value for optional options');
        $this->assertEquals(['name' => 'foo', 'bar' => 'default'], $input->getOptions(), '->getOptions() returns all option values, even optional ones');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo', '--bar' => ''], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('bar', '', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertEquals('', $input->getOption('bar'), '->getOption() returns null for options explicitly passed without value (or an empty value)');
        $this->assertEquals(['name' => 'foo', 'bar' => ''], $input->getOptions(), '->getOptions() returns all option values.');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo', '--bar' => null], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('bar', '', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertNull($input->getOption('bar'), '->getOption() returns null for options explicitly passed without value (or an empty value)');
        $this->assertEquals(['name' => 'foo', 'bar' => null], $input->getOptions(), '->getOptions() returns all option values');
    }
    public function testSetInvalidOption()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "foo" option does not exist.');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('bar', '', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')]));
        $input->setOption('foo', 'bar');
    }
    public function testGetInvalidOption()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "foo" option does not exist.');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputOption('bar', '', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')]));
        $input->getOption('foo');
    }
    public function testArguments()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name')]));
        $this->assertEquals('foo', $input->getArgument('name'), '->getArgument() returns the value for the given argument');
        $input->setArgument('name', 'bar');
        $this->assertEquals('bar', $input->getArgument('name'), '->setArgument() sets the value for a given argument');
        $this->assertEquals(['name' => 'bar'], $input->getArguments(), '->getArguments() returns all argument values');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('bar', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, '', 'default')]));
        $this->assertEquals('default', $input->getArgument('bar'), '->getArgument() returns the default value for optional arguments');
        $this->assertEquals(['name' => 'foo', 'bar' => 'default'], $input->getArguments(), '->getArguments() returns all argument values, even optional ones');
    }
    public function testSetInvalidArgument()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "foo" argument does not exist.');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('bar', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, '', 'default')]));
        $input->setArgument('foo', 'bar');
    }
    public function testGetInvalidArgument()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "foo" argument does not exist.');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name'), new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('bar', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL, '', 'default')]));
        $input->getArgument('foo');
    }
    public function testValidateWithMissingArguments()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput([]);
        $input->bind(new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED)]));
        $input->validate();
    }
    public function testValidateWithMissingRequiredArguments()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['bar' => 'baz']);
        $input->bind(new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED), new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('bar', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::OPTIONAL)]));
        $input->validate();
    }
    public function testValidate()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo']);
        $input->bind(new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name', \MolliePrefix\Symfony\Component\Console\Input\InputArgument::REQUIRED)]));
        $this->assertNull($input->validate());
    }
    public function testSetGetInteractive()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput([]);
        $this->assertTrue($input->isInteractive(), '->isInteractive() returns whether the input should be interactive or not');
        $input->setInteractive(\false);
        $this->assertFalse($input->isInteractive(), '->setInteractive() changes the interactive flag');
    }
    public function testSetGetStream()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput([]);
        $stream = \fopen('php://memory', 'r+', \false);
        $input->setStream($stream);
        $this->assertSame($stream, $input->getStream());
    }
}
