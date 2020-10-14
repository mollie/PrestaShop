<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Command;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Application;
use MolliePrefix\Symfony\Component\Console\Command\HelpCommand;
use MolliePrefix\Symfony\Component\Console\Command\ListCommand;
use MolliePrefix\Symfony\Component\Console\Tester\CommandTester;
class HelpCommandTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testExecuteForCommandAlias()
    {
        $command = new \MolliePrefix\Symfony\Component\Console\Command\HelpCommand();
        $command->setApplication(new \MolliePrefix\Symfony\Component\Console\Application());
        $commandTester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->execute(['command_name' => 'li'], ['decorated' => \false]);
        $this->assertStringContainsString('list [options] [--] [<namespace>]', $commandTester->getDisplay(), '->execute() returns a text help for the given command alias');
        $this->assertStringContainsString('format=FORMAT', $commandTester->getDisplay(), '->execute() returns a text help for the given command alias');
        $this->assertStringContainsString('raw', $commandTester->getDisplay(), '->execute() returns a text help for the given command alias');
    }
    public function testExecuteForCommand()
    {
        $command = new \MolliePrefix\Symfony\Component\Console\Command\HelpCommand();
        $commandTester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $command->setCommand(new \MolliePrefix\Symfony\Component\Console\Command\ListCommand());
        $commandTester->execute([], ['decorated' => \false]);
        $this->assertStringContainsString('list [options] [--] [<namespace>]', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
        $this->assertStringContainsString('format=FORMAT', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
        $this->assertStringContainsString('raw', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
    }
    public function testExecuteForCommandWithXmlOption()
    {
        $command = new \MolliePrefix\Symfony\Component\Console\Command\HelpCommand();
        $commandTester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($command);
        $command->setCommand(new \MolliePrefix\Symfony\Component\Console\Command\ListCommand());
        $commandTester->execute(['--format' => 'xml']);
        $this->assertStringContainsString('<command', $commandTester->getDisplay(), '->execute() returns an XML help text if --xml is passed');
    }
    public function testExecuteForApplicationCommand()
    {
        $application = new \MolliePrefix\Symfony\Component\Console\Application();
        $commandTester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($application->get('help'));
        $commandTester->execute(['command_name' => 'list']);
        $this->assertStringContainsString('list [options] [--] [<namespace>]', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
        $this->assertStringContainsString('format=FORMAT', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
        $this->assertStringContainsString('raw', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
    }
    public function testExecuteForApplicationCommandWithXmlOption()
    {
        $application = new \MolliePrefix\Symfony\Component\Console\Application();
        $commandTester = new \MolliePrefix\Symfony\Component\Console\Tester\CommandTester($application->get('help'));
        $commandTester->execute(['command_name' => 'list', '--format' => 'xml']);
        $this->assertStringContainsString('list [--raw] [--format FORMAT] [--] [&lt;namespace&gt;]', $commandTester->getDisplay(), '->execute() returns a text help for the given command');
        $this->assertStringContainsString('<command', $commandTester->getDisplay(), '->execute() returns an XML help text if --format=xml is passed');
    }
}
