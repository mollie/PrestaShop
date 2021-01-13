<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Process\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Process\ProcessBuilder;
/**
 * @group legacy
 */
class ProcessBuilderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testInheritEnvironmentVars()
    {
        $proc = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create()->add('foo')->getProcess();
        $this->assertTrue($proc->areEnvironmentVariablesInherited());
        $proc = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create()->add('foo')->inheritEnvironmentVariables(\false)->getProcess();
        $this->assertFalse($proc->areEnvironmentVariablesInherited());
    }
    public function testAddEnvironmentVariables()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder();
        $env = ['foo' => 'bar', 'foo2' => 'bar2'];
        $proc = $pb->add('command')->setEnv('foo', 'bar2')->addEnvironmentVariables($env)->getProcess();
        $this->assertSame($env, $proc->getEnv());
    }
    public function testNegativeTimeoutFromSetter()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Process\\Exception\\InvalidArgumentException');
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder();
        $pb->setTimeout(-1);
    }
    public function testNullTimeout()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder();
        $pb->setTimeout(10);
        $pb->setTimeout(null);
        $r = new \ReflectionObject($pb);
        $p = $r->getProperty('timeout');
        $p->setAccessible(\true);
        $this->assertNull($p->getValue($pb));
    }
    public function testShouldSetArguments()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder(['initial']);
        $pb->setArguments(['second']);
        $proc = $pb->getProcess();
        $this->assertStringContainsString('second', $proc->getCommandLine());
    }
    public function testPrefixIsPrependedToAllGeneratedProcess()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder();
        $pb->setPrefix('/usr/bin/php');
        $proc = $pb->setArguments(['-v'])->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" -v', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' '-v'", $proc->getCommandLine());
        }
        $proc = $pb->setArguments(['-i'])->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" -i', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' '-i'", $proc->getCommandLine());
        }
    }
    public function testArrayPrefixesArePrependedToAllGeneratedProcess()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder();
        $pb->setPrefix(['/usr/bin/php', 'composer.phar']);
        $proc = $pb->setArguments(['-v'])->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" composer.phar -v', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' 'composer.phar' '-v'", $proc->getCommandLine());
        }
        $proc = $pb->setArguments(['-i'])->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" composer.phar -i', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' 'composer.phar' '-i'", $proc->getCommandLine());
        }
    }
    public function testShouldEscapeArguments()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder(['%path%', 'foo " bar', '%baz%baz']);
        $proc = $pb->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertSame('""^%"path"^%"" "foo "" bar" ""^%"baz"^%"baz"', $proc->getCommandLine());
        } else {
            $this->assertSame("'%path%' 'foo \" bar' '%baz%baz'", $proc->getCommandLine());
        }
    }
    public function testShouldEscapeArgumentsAndPrefix()
    {
        $pb = new \MolliePrefix\Symfony\Component\Process\ProcessBuilder(['arg']);
        $pb->setPrefix('%prefix%');
        $proc = $pb->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertSame('""^%"prefix"^%"" arg', $proc->getCommandLine());
        } else {
            $this->assertSame("'%prefix%' 'arg'", $proc->getCommandLine());
        }
    }
    public function testShouldThrowALogicExceptionIfNoPrefixAndNoArgument()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Process\\Exception\\LogicException');
        \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create()->getProcess();
    }
    public function testShouldNotThrowALogicExceptionIfNoArgument()
    {
        $process = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create()->setPrefix('/usr/bin/php')->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php'", $process->getCommandLine());
        }
    }
    public function testShouldNotThrowALogicExceptionIfNoPrefix()
    {
        $process = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create(['/usr/bin/php'])->getProcess();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php'", $process->getCommandLine());
        }
    }
    public function testShouldReturnProcessWithDisabledOutput()
    {
        $process = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create(['/usr/bin/php'])->disableOutput()->getProcess();
        $this->assertTrue($process->isOutputDisabled());
    }
    public function testShouldReturnProcessWithEnabledOutput()
    {
        $process = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create(['/usr/bin/php'])->disableOutput()->enableOutput()->getProcess();
        $this->assertFalse($process->isOutputDisabled());
    }
    public function testInvalidInput()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Process\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('"Symfony\\Component\\Process\\ProcessBuilder::setInput" only accepts strings, Traversable objects or stream resources.');
        $builder = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create();
        $builder->setInput([]);
    }
    public function testDoesNotPrefixExec()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('This test cannot run on Windows.');
        }
        $builder = \MolliePrefix\Symfony\Component\Process\ProcessBuilder::create(['command', '-v', 'ls']);
        $process = $builder->getProcess();
        $process->run();
        $this->assertTrue($process->isSuccessful());
    }
}
