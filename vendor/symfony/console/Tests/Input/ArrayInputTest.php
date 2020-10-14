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
class ArrayInputTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetFirstArgument()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput([]);
        $this->assertNull($input->getFirstArgument(), '->getFirstArgument() returns null if no argument were passed');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'Fabien']);
        $this->assertEquals('Fabien', $input->getFirstArgument(), '->getFirstArgument() returns the first passed argument');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--foo' => 'bar', 'name' => 'Fabien']);
        $this->assertEquals('Fabien', $input->getFirstArgument(), '->getFirstArgument() returns the first passed argument');
    }
    public function testHasParameterOption()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'Fabien', '--foo' => 'bar']);
        $this->assertTrue($input->hasParameterOption('--foo'), '->hasParameterOption() returns true if an option is present in the passed parameters');
        $this->assertFalse($input->hasParameterOption('--bar'), '->hasParameterOption() returns false if an option is not present in the passed parameters');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--foo']);
        $this->assertTrue($input->hasParameterOption('--foo'), '->hasParameterOption() returns true if an option is present in the passed parameters');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--foo', '--', '--bar']);
        $this->assertTrue($input->hasParameterOption('--bar'), '->hasParameterOption() returns true if an option is present in the passed parameters');
        $this->assertFalse($input->hasParameterOption('--bar', \true), '->hasParameterOption() returns false if an option is present in the passed parameters after an end of options signal');
    }
    public function testGetParameterOption()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'Fabien', '--foo' => 'bar']);
        $this->assertEquals('bar', $input->getParameterOption('--foo'), '->getParameterOption() returns the option of specified name');
        $this->assertEquals('default', $input->getParameterOption('--bar', 'default'), '->getParameterOption() returns the default value if an option is not present in the passed parameters');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['Fabien', '--foo' => 'bar']);
        $this->assertEquals('bar', $input->getParameterOption('--foo'), '->getParameterOption() returns the option of specified name');
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['--foo', '--', '--bar' => 'woop']);
        $this->assertEquals('woop', $input->getParameterOption('--bar'), '->getParameterOption() returns the correct value if an option is present in the passed parameters');
        $this->assertEquals('default', $input->getParameterOption('--bar', 'default', \true), '->getParameterOption() returns the default value if an option is present in the passed parameters after an end of options signal');
    }
    public function testParseArguments()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['name' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name')]));
        $this->assertEquals(['name' => 'foo'], $input->getArguments(), '->parse() parses required arguments');
    }
    /**
     * @dataProvider provideOptions
     */
    public function testParseOptions($input, $options, $expectedOptions, $message)
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput($input, new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition($options));
        $this->assertEquals($expectedOptions, $input->getOptions(), $message);
    }
    public function provideOptions()
    {
        return [[['--foo' => 'bar'], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo')], ['foo' => 'bar'], '->parse() parses long options'], [['--foo' => 'bar'], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')], ['foo' => 'bar'], '->parse() parses long options with a default value'], [[], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')], ['foo' => 'default'], '->parse() uses the default value for long options with value optional which are not passed'], [['--foo' => null], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')], ['foo' => null], '->parse() parses long options with a default value'], [['-f' => 'bar'], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f')], ['foo' => 'bar'], '->parse() parses short options'], [['--' => null, '-f' => 'bar'], [new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, '', 'default')], ['foo' => 'default'], '->parse() does not parse opts after an end of options signal'], [['--' => null], [], [], '->parse() does not choke on end of options signal']];
    }
    /**
     * @dataProvider provideInvalidInput
     */
    public function testParseInvalidInput($parameters, $definition, $expectedExceptionMessage)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput($parameters, $definition);
    }
    public function provideInvalidInput()
    {
        return [[['foo' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputArgument('name')]), 'The "foo" argument does not exist.'], [['--foo' => null], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition([new \MolliePrefix\Symfony\Component\Console\Input\InputOption('foo', 'f', \MolliePrefix\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED)]), 'The "--foo" option requires a value.'], [['--foo' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition(), 'The "--foo" option does not exist.'], [['-o' => 'foo'], new \MolliePrefix\Symfony\Component\Console\Input\InputDefinition(), 'The "-o" option does not exist.']];
    }
    public function testToString()
    {
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['-f' => null, '-b' => 'bar', '--foo' => 'b a z', '--lala' => null, 'test' => 'Foo', 'test2' => "A\nB'C"]);
        $this->assertEquals('-f -b=bar --foo=' . \escapeshellarg('b a z') . ' --lala Foo ' . \escapeshellarg("A\nB'C"), (string) $input);
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['-b' => ['bval_1', 'bval_2'], '--f' => ['fval_1', 'fval_2']]);
        $this->assertSame('-b=bval_1 -b=bval_2 --f=fval_1 --f=fval_2', (string) $input);
        $input = new \MolliePrefix\Symfony\Component\Console\Input\ArrayInput(['array_arg' => ['val_1', 'val_2']]);
        $this->assertSame('val_1 val_2', (string) $input);
    }
}
