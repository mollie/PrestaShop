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
namespace MolliePrefix\PhpCsFixer\Linter;

use MolliePrefix\Symfony\Component\Process\Process;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ProcessLintingResult implements \MolliePrefix\PhpCsFixer\Linter\LintingResultInterface
{
    /**
     * @var bool
     */
    private $isSuccessful;
    /**
     * @var Process
     */
    private $process;
    public function __construct(\MolliePrefix\Symfony\Component\Process\Process $process)
    {
        $this->process = $process;
    }
    /**
     * {@inheritdoc}
     */
    public function check()
    {
        if (!$this->isSuccessful()) {
            // on some systems stderr is used, but on others, it's not
            throw new \MolliePrefix\PhpCsFixer\Linter\LintingException($this->process->getErrorOutput() ?: $this->process->getOutput(), $this->process->getExitCode());
        }
    }
    private function isSuccessful()
    {
        if (null === $this->isSuccessful) {
            $this->process->wait();
            $this->isSuccessful = $this->process->isSuccessful();
        }
        return $this->isSuccessful;
    }
}
