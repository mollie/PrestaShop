<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Logger;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Psr\Log\LoggerInterface;
use MolliePrefix\Psr\Log\LogLevel;
use MolliePrefix\Symfony\Component\Console\Logger\ConsoleLogger;
use MolliePrefix\Symfony\Component\Console\Output\BufferedOutput;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DummyOutput;
/**
 * Console logger test.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ConsoleLoggerTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @var DummyOutput
     */
    protected $output;
    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        $this->output = new \MolliePrefix\Symfony\Component\Console\Tests\Fixtures\DummyOutput(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE);
        return new \MolliePrefix\Symfony\Component\Console\Logger\ConsoleLogger($this->output, [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::ALERT => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::CRITICAL => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::ERROR => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::WARNING => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::NOTICE => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::INFO => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \MolliePrefix\Psr\Log\LogLevel::DEBUG => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL]);
    }
    /**
     * Return the log messages in order.
     *
     * @return string[]
     */
    public function getLogs()
    {
        return $this->output->getLogs();
    }
    /**
     * @dataProvider provideOutputMappingParams
     */
    public function testOutputMapping($logLevel, $outputVerbosity, $isOutput, $addVerbosityLevelMap = [])
    {
        $out = new \MolliePrefix\Symfony\Component\Console\Output\BufferedOutput($outputVerbosity);
        $logger = new \MolliePrefix\Symfony\Component\Console\Logger\ConsoleLogger($out, $addVerbosityLevelMap);
        $logger->log($logLevel, 'foo bar');
        $logs = $out->fetch();
        $this->assertEquals($isOutput ? "[{$logLevel}] foo bar" . \PHP_EOL : '', $logs);
    }
    public function provideOutputMappingParams()
    {
        $quietMap = [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY => \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET];
        return [[\MolliePrefix\Psr\Log\LogLevel::EMERGENCY, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \true], [\MolliePrefix\Psr\Log\LogLevel::WARNING, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \true], [\MolliePrefix\Psr\Log\LogLevel::INFO, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \false], [\MolliePrefix\Psr\Log\LogLevel::DEBUG, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \false], [\MolliePrefix\Psr\Log\LogLevel::INFO, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE, \false], [\MolliePrefix\Psr\Log\LogLevel::INFO, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE, \true], [\MolliePrefix\Psr\Log\LogLevel::DEBUG, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE, \false], [\MolliePrefix\Psr\Log\LogLevel::DEBUG, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG, \true], [\MolliePrefix\Psr\Log\LogLevel::ALERT, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET, \false], [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET, \false], [\MolliePrefix\Psr\Log\LogLevel::ALERT, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET, \false, $quietMap], [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET, \true, $quietMap]];
    }
    public function testHasErrored()
    {
        $logger = new \MolliePrefix\Symfony\Component\Console\Logger\ConsoleLogger(new \MolliePrefix\Symfony\Component\Console\Output\BufferedOutput());
        $this->assertFalse($logger->hasErrored());
        $logger->warning('foo');
        $this->assertFalse($logger->hasErrored());
        $logger->error('bar');
        $this->assertTrue($logger->hasErrored());
    }
    public function testImplements()
    {
        $this->assertInstanceOf('MolliePrefix\\Psr\\Log\\LoggerInterface', $this->getLogger());
    }
    /**
     * @dataProvider provideLevelsAndMessages
     */
    public function testLogsAtAllLevels($level, $message)
    {
        $logger = $this->getLogger();
        $logger->{$level}($message, ['user' => 'Bob']);
        $logger->log($level, $message, ['user' => 'Bob']);
        $expected = [$level . ' message of level ' . $level . ' with context: Bob', $level . ' message of level ' . $level . ' with context: Bob'];
        $this->assertEquals($expected, $this->getLogs());
    }
    public function provideLevelsAndMessages()
    {
        return [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY => [\MolliePrefix\Psr\Log\LogLevel::EMERGENCY, 'message of level emergency with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::ALERT => [\MolliePrefix\Psr\Log\LogLevel::ALERT, 'message of level alert with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::CRITICAL => [\MolliePrefix\Psr\Log\LogLevel::CRITICAL, 'message of level critical with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::ERROR => [\MolliePrefix\Psr\Log\LogLevel::ERROR, 'message of level error with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::WARNING => [\MolliePrefix\Psr\Log\LogLevel::WARNING, 'message of level warning with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::NOTICE => [\MolliePrefix\Psr\Log\LogLevel::NOTICE, 'message of level notice with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::INFO => [\MolliePrefix\Psr\Log\LogLevel::INFO, 'message of level info with context: {user}'], \MolliePrefix\Psr\Log\LogLevel::DEBUG => [\MolliePrefix\Psr\Log\LogLevel::DEBUG, 'message of level debug with context: {user}']];
    }
    public function testThrowsOnInvalidLevel()
    {
        $this->expectException('MolliePrefix\\Psr\\Log\\InvalidArgumentException');
        $logger = $this->getLogger();
        $logger->log('invalid level', 'Foo');
    }
    public function testContextReplacement()
    {
        $logger = $this->getLogger();
        $logger->info('{Message {nothing} {user} {foo.bar} a}', ['user' => 'Bob', 'foo.bar' => 'Bar']);
        $expected = ['info {Message {nothing} Bob Bar a}'];
        $this->assertEquals($expected, $this->getLogs());
    }
    public function testObjectCastToString()
    {
        if (\method_exists($this, 'createPartialMock')) {
            $dummy = $this->createPartialMock('MolliePrefix\\Symfony\\Component\\Console\\Tests\\Logger\\DummyTest', ['__toString']);
        } else {
            $dummy = $this->createPartialMock('MolliePrefix\\Symfony\\Component\\Console\\Tests\\Logger\\DummyTest', ['__toString']);
        }
        $dummy->method('__toString')->willReturn('DUMMY');
        $this->getLogger()->warning($dummy);
        $expected = ['warning DUMMY'];
        $this->assertEquals($expected, $this->getLogs());
    }
    public function testContextCanContainAnything()
    {
        $context = ['bool' => \true, 'null' => null, 'string' => 'Foo', 'int' => 0, 'float' => 0.5, 'nested' => ['with object' => new \MolliePrefix\Symfony\Component\Console\Tests\Logger\DummyTest()], 'object' => new \DateTime(), 'resource' => \fopen('php://memory', 'r')];
        $this->getLogger()->warning('Crazy context data', $context);
        $expected = ['warning Crazy context data'];
        $this->assertEquals($expected, $this->getLogs());
    }
    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $logger = $this->getLogger();
        $logger->warning('Random message', ['exception' => 'oops']);
        $logger->critical('Uncaught Exception!', ['exception' => new \LogicException('Fail')]);
        $expected = ['warning Random message', 'critical Uncaught Exception!'];
        $this->assertEquals($expected, $this->getLogs());
    }
}
class DummyTest
{
    public function __toString()
    {
    }
}
