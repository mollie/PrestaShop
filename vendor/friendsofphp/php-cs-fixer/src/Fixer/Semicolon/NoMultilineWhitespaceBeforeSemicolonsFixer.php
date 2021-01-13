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
namespace MolliePrefix\PhpCsFixer\Fixer\Semicolon;

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * @deprecated since 2.9.1, replaced by MultilineWhitespaceBeforeSemicolonsFixer
 *
 * @todo To be removed at 3.0
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
final class NoMultilineWhitespaceBeforeSemicolonsFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Multi-line whitespace before closing semicolon are prohibited.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
function foo () {
    return 1 + 2
        ;
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after SimplifiedIfReturnFixer.
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
    /**
     * {@inheritdoc}
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
        $fixer = new \MolliePrefix\PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer();
        $fixer->configure(['strategy' => \MolliePrefix\PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer::STRATEGY_NO_MULTI_LINE]);
        return [$fixer];
    }
}
