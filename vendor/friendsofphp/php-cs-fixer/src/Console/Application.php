<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace MolliePrefix\PhpCsFixer\Console;

use MolliePrefix\PhpCsFixer\Console\Command\DescribeCommand;
use MolliePrefix\PhpCsFixer\Console\Command\FixCommand;
use MolliePrefix\PhpCsFixer\Console\Command\HelpCommand;
use MolliePrefix\PhpCsFixer\Console\Command\SelfUpdateCommand;
use MolliePrefix\PhpCsFixer\Console\SelfUpdate\GithubClient;
use MolliePrefix\PhpCsFixer\Console\SelfUpdate\NewVersionChecker;
use MolliePrefix\PhpCsFixer\PharChecker;
use MolliePrefix\PhpCsFixer\ToolInfo;
use MolliePrefix\Symfony\Component\Console\Application as BaseApplication;
use MolliePrefix\Symfony\Component\Console\Command\ListCommand;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class Application extends \MolliePrefix\Symfony\Component\Console\Application
{
    const VERSION = '2.17.3';
    const VERSION_CODENAME = 'Desert Beast';
    /**
     * @var ToolInfo
     */
    private $toolInfo;
    public function __construct()
    {
        if (!\getenv('PHP_CS_FIXER_FUTURE_MODE')) {
            \error_reporting(\E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED);
        }
        parent::__construct('PHP CS Fixer', self::VERSION);
        $this->toolInfo = new \MolliePrefix\PhpCsFixer\ToolInfo();
        $this->add(new \MolliePrefix\PhpCsFixer\Console\Command\DescribeCommand());
        $this->add(new \MolliePrefix\PhpCsFixer\Console\Command\FixCommand($this->toolInfo));
        $this->add(new \MolliePrefix\PhpCsFixer\Console\Command\SelfUpdateCommand(new \MolliePrefix\PhpCsFixer\Console\SelfUpdate\NewVersionChecker(new \MolliePrefix\PhpCsFixer\Console\SelfUpdate\GithubClient()), $this->toolInfo, new \MolliePrefix\PhpCsFixer\PharChecker()));
    }
    /**
     * @return int
     */
    public static function getMajorVersion()
    {
        return (int) \explode('.', self::VERSION)[0];
    }
    /**
     * {@inheritdoc}
     */
    public function doRun(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $stdErr = $output instanceof \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface ? $output->getErrorOutput() : ($input->hasParameterOption('--format', \true) && 'txt' !== $input->getParameterOption('--format', null, \true) ? null : $output);
        if (null !== $stdErr) {
            $warningsDetector = new \MolliePrefix\PhpCsFixer\Console\WarningsDetector($this->toolInfo);
            $warningsDetector->detectOldVendor();
            $warningsDetector->detectOldMajor();
            foreach ($warningsDetector->getWarnings() as $warning) {
                $stdErr->writeln(\sprintf($stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s', $warning));
            }
        }
        return parent::doRun($input, $output);
    }
    /**
     * {@inheritdoc}
     */
    public function getLongVersion()
    {
        $version = \sprintf('%s <info>%s</info> by <comment>Fabien Potencier</comment> and <comment>Dariusz Ruminski</comment>', parent::getLongVersion(), self::VERSION_CODENAME);
        $commit = '@git-commit@';
        if ('@' . 'git-commit@' !== $commit) {
            $version .= ' (' . \substr($commit, 0, 7) . ')';
        }
        return $version;
    }
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return [new \MolliePrefix\PhpCsFixer\Console\Command\HelpCommand(), new \MolliePrefix\Symfony\Component\Console\Command\ListCommand()];
    }
}
