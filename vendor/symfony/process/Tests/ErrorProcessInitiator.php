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

use MolliePrefix\Symfony\Component\Process\Exception\ProcessTimedOutException;
use MolliePrefix\Symfony\Component\Process\Process;
require \dirname(__DIR__) . '/vendor/autoload.php';
list('e' => $php) = \getopt('e:') + ['e' => 'php'];
try {
    $process = new \MolliePrefix\Symfony\Component\Process\Process("exec {$php} -r \"echo 'ready'; trigger_error('error', E_USER_ERROR);\"");
    $process->start();
    $process->setTimeout(0.5);
    while (\false === \strpos($process->getOutput(), 'ready')) {
        \usleep(1000);
    }
    $process->signal(\SIGSTOP);
    $process->wait();
    return $process->getExitCode();
} catch (\MolliePrefix\Symfony\Component\Process\Exception\ProcessTimedOutException $t) {
    echo $t->getMessage() . \PHP_EOL;
    return 1;
}
