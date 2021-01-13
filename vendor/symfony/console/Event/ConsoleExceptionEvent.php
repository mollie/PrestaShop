<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Event;

@\trigger_error(\sprintf('The "%s" class is deprecated since Symfony 3.3 and will be removed in 4.0. Use the ConsoleErrorEvent instead.', \MolliePrefix\Symfony\Component\Console\Event\ConsoleExceptionEvent::class), \E_USER_DEPRECATED);
use MolliePrefix\Symfony\Component\Console\Command\Command;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
/**
 * Allows to handle exception thrown in a command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated since version 3.3, to be removed in 4.0. Use ConsoleErrorEvent instead.
 */
class ConsoleExceptionEvent extends \MolliePrefix\Symfony\Component\Console\Event\ConsoleEvent
{
    private $exception;
    private $exitCode;
    public function __construct(\MolliePrefix\Symfony\Component\Console\Command\Command $command, \MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, \Exception $exception, $exitCode)
    {
        parent::__construct($command, $input, $output);
        $this->setException($exception);
        $this->exitCode = (int) $exitCode;
    }
    /**
     * Returns the thrown exception.
     *
     * @return \Exception The thrown exception
     */
    public function getException()
    {
        return $this->exception;
    }
    /**
     * Replaces the thrown exception.
     *
     * This exception will be thrown if no response is set in the event.
     *
     * @param \Exception $exception The thrown exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
