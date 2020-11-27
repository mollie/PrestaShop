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
namespace MolliePrefix\PhpCsFixer\Fixer\ClassNotation;

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * @author SpacePossum
 *
 * @deprecated in 2.8, proxy to ClassAttributesSeparationFixer
 */
final class MethodSeparationFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Methods must be separated with one blank line.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Sample
{
    protected function foo()
    {
    }
    protected function bar()
    {
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer, IndentationTypeFixer.
     * Must run after OrderedClassElementsFixer.
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
    /**
     * Returns names of fixers to use instead, if any.
     *
     * @return string[]
     */
    public function getSuccessorsNames()
    {
        return \array_keys($this->proxyFixers);
    }
    /**
     * {@inheritdoc}
     */
    protected function createProxyFixers()
    {
        $fixer = new \MolliePrefix\PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer();
        $fixer->configure(['elements' => ['method']]);
        return [$fixer];
    }
}
