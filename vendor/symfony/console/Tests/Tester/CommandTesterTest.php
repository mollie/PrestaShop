<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Tester;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Application;
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Helper\HelperSet;
use MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper;
use MolliePrefix\Symfony\Component\Console\Output\Output;
use MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion;
use MolliePrefix\Symfony\Component\Console\Question\Question;
use MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle;
use MolliePrefix\Symfony\Component\Console\Tester\CommandTester;
class CommandTesterTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    protected $command;
    protected $tester;
    protected function setUp()
    {
        $this->command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $this->command->addArgument('command');
        $this->command->addArgument('foo');
        $this->command->setCode(function ($input, $output) {
            $output->writeln('foo');
        });
        $this->tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($this->command);
        $this->tester->execute(['foo' => 'bar'], ['interactive' => \false, 'decorated' => \false, 'verbosity' => \MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_VERBOSE]);
    }
    protected function tearDown()
    {
        $this->command = null;
        $this->tester = null;
    }
    public function testExecute()
    {
        $this->assertFalse($this->tester->getInput()->isInteractive(), '->execute() takes an interactive option');
        $this->assertFalse($this->tester->getOutput()->isDecorated(), '->execute() takes a decorated option');
        $this->assertEquals(\MolliePrefix\Symfony\Component\Console\Output\Output::VERBOSITY_VERBOSE, $this->tester->getOutput()->getVerbosity(), '->execute() takes a verbosity option');
    }
    public function testGetInput()
    {
        $this->assertEquals('bar', $this->tester->getInput()->getArgument('foo'), '->getInput() returns the current input instance');
    }
    public function testGetOutput()
    {
        \rewind($this->tester->getOutput()->getStream());
        $this->assertEquals('foo' . \PHP_EOL, \stream_get_contents($this->tester->getOutput()->getStream()), '->getOutput() returns the current output instance');
    }
    public function testGetDisplay()
    {
        $this->assertEquals('foo' . \PHP_EOL, $this->tester->getDisplay(), '->getDisplay() returns the display of the last execution');
    }
    public function testGetStatusCode()
    {
        $this->assertSame(0, $this->tester->getStatusCode(), '->getStatusCode() returns the status code');
    }
    public function testCommandFromApplication()
    {
        $application = new \MolliePrefix\Symfony\Component\Console\Application();
        $application->setAutoExit(\false);
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setCode(function ($input, $output) {
            $output->writeln('foo');
        });
        $application->add($command);
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($application->find('foo'));
        // check that there is no need to pass the command name here
        $this->assertEquals(0, $tester->execute([]));
    }
    public function testCommandWithInputs()
    {
        $questions = ['What\'s your name?', 'How are you?', 'Where do you come from?'];
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setHelperSet(new \MolliePrefix\Symfony\Component\Console\Helper\HelperSet([new \MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper()]));
        $command->setCode(function ($input, $output) use($questions, $command) {
            $helper = $command->getHelper('question');
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[0]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[1]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[2]));
        });
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $tester->setInputs(['Bobby', 'Fine', 'France']);
        $tester->execute([]);
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals(\implode('', $questions), $tester->getDisplay(\true));
    }
    public function testCommandWithDefaultInputs()
    {
        $questions = ['What\'s your name?', 'How are you?', 'Where do you come from?'];
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setHelperSet(new \MolliePrefix\Symfony\Component\Console\Helper\HelperSet([new \MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper()]));
        $command->setCode(function ($input, $output) use($questions, $command) {
            $helper = $command->getHelper('question');
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[0], 'Bobby'));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[1], 'Fine'));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[2], 'France'));
        });
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $tester->setInputs(['', '', '']);
        $tester->execute([]);
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals(\implode('', $questions), $tester->getDisplay(\true));
    }
    public function testCommandWithWrongInputsNumber()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Aborted.');
        $questions = ['What\'s your name?', 'How are you?', 'Where do you come from?'];
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setHelperSet(new \MolliePrefix\Symfony\Component\Console\Helper\HelperSet([new \MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper()]));
        $command->setCode(function ($input, $output) use($questions, $command) {
            $helper = $command->getHelper('question');
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion('choice', ['a', 'b']));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[0]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[1]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[2]));
        });
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $tester->setInputs(['a', 'Bobby', 'Fine']);
        $tester->execute([]);
    }
    public function testCommandWithQuestionsButNoInputs()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Aborted.');
        $questions = ['What\'s your name?', 'How are you?', 'Where do you come from?'];
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setHelperSet(new \MolliePrefix\Symfony\Component\Console\Helper\HelperSet([new \MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper()]));
        $command->setCode(function ($input, $output) use($questions, $command) {
            $helper = $command->getHelper('question');
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion('choice', ['a', 'b']));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[0]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[1]));
            $helper->ask($input, $output, new \MolliePrefix\Symfony\Component\Console\Question\Question($questions[2]));
        });
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $tester->execute([]);
    }
    public function testSymfonyStyleCommandWithInputs()
    {
        $questions = ['What\'s your name?', 'How are you?', 'Where do you come from?'];
        $command = new \MolliePrefix\Symfony\Component\Console\Command\Command('foo');
        $command->setCode(function ($input, $output) use($questions) {
            $io = new \MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
            $io->ask($questions[0]);
            $io->ask($questions[1]);
            $io->ask($questions[2]);
        });
        $tester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $tester->setInputs(['Bobby', 'Fine', 'France']);
        $tester->execute([]);
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
