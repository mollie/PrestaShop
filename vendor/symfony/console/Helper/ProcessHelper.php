<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Helper;

use MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Process\Exception\ProcessFailedException;
use MolliePrefix\Symfony\Component\Process\Process;
/**
 * The ProcessHelper class provides helpers to run external processes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ProcessHelper extends \MolliePrefix\Symfony\Component\Console\Helper\Helper
{
    /**
     * Runs an external process.
     *
     * @param OutputInterface      $output    An OutputInterface instance
     * @param string|array|Process $cmd       An instance of Process or an array of arguments to escape and run or a command to run
     * @param string|null          $error     An error message that must be displayed if something went wrong
     * @param callable|null        $callback  A PHP callback to run whenever there is some
     *                                        output available on STDOUT or STDERR
     * @param int                  $verbosity The threshold for verbosity
     *
     * @return Process The process that ran
     */
    public function run(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $cmd, $error = null, callable $callback = null, $verbosity = \MolliePrefix\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE)
    {
        if (!\class_exists(\MolliePrefix\Symfony\Component\Process\Process::class)) {
            throw new \LogicException('The ProcessHelper cannot be run as the Process component is not installed. Try running "compose require symfony/process".');
        }
        if ($output instanceof \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        if ($cmd instanceof \MolliePrefix\Symfony\Component\Process\Process) {
            $process = $cmd;
        } else {
            $process = new \MolliePrefix\Symfony\Component\Process\Process($cmd);
        }
        if ($verbosity <= $output->getVerbosity()) {
            $output->write($formatter->start(\spl_object_hash($process), $this->escapeString($process->getCommandLine())));
        }
        if ($output->isDebug()) {
            $callback = $this->wrapCallback($output, $process, $callback);
        }
        $process->run($callback);
        if ($verbosity <= $output->getVerbosity()) {
            $message = $process->isSuccessful() ? 'Command ran successfully' : \sprintf('%s Command did not run successfully', $process->getExitCode());
            $output->write($formatter->stop(\spl_object_hash($process), $message, $process->isSuccessful()));
        }
        if (!$process->isSuccessful() && null !== $error) {
            $output->writeln(\sprintf('<error>%s</error>', $this->escapeString($error)));
        }
        return $process;
    }
    /**
     * Runs the process.
     *
     * This is identical to run() except that an exception is thrown if the process
     * exits with a non-zero exit code.
     *
     * @param OutputInterface $output   An OutputInterface instance
     * @param string|Process  $cmd      An instance of Process or a command to run
     * @param string|null     $error    An error message that must be displayed if something went wrong
     * @param callable|null   $callback A PHP callback to run whenever there is some
     *                                  output available on STDOUT or STDERR
     *
     * @return Process The process that ran
     *
     * @throws ProcessFailedException
     *
     * @see run()
     */
    public function mustRun(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, $cmd, $error = null, callable $callback = null)
    {
        $process = $this->run($output, $cmd, $error, $callback);
        if (!$process->isSuccessful()) {
            throw new \MolliePrefix\Symfony\Component\Process\Exception\ProcessFailedException($process);
        }
        return $process;
    }
    /**
     * Wraps a Process callback to add debugging output.
     *
     * @param OutputInterface $output   An OutputInterface interface
     * @param Process         $process  The Process
     * @param callable|null   $callback A PHP callable
     *
     * @return callable
     */
    public function wrapCallback(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, \MolliePrefix\Symfony\Component\Process\Process $process, callable $callback = null)
    {
        if ($output instanceof \MolliePrefix\Symfony\Component\Console\Output\ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $formatter = $this->getHelperSet()->get('debug_formatter');
        return function ($type, $buffer) use($output, $process, $callback, $formatter) {
            $output->write($formatter->progress(\spl_object_hash($process), $this->escapeString($buffer), \MolliePrefix\Symfony\Component\Process\Process::ERR === $type));
            if (null !== $callback) {
                \call_user_func($callback, $type, $buffer);
            }
        };
    }
    private function escapeString($str)
    {
        return \str_replace('<', '\\<', $str);
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'process';
    }
}
