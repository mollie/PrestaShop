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
namespace MolliePrefix\PhpCsFixer\Fixer\NamespaceNotation;

use MolliePrefix\PhpCsFixer\AbstractLinesBeforeNamespaceFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 */
final class SingleBlankLineBeforeNamespaceFixer extends \MolliePrefix\PhpCsFixer\AbstractLinesBeforeNamespaceFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There should be exactly one blank line before a namespace declaration.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php  namespace A {}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\n\nnamespace A{}\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_NAMESPACE);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoBlankLinesAfterPhpdocFixer.
     */
    public function getPriority()
    {
        return -21;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            $token = $tokens[$index];
            if ($token->isGivenKind(\T_NAMESPACE)) {
                $this->fixLinesBeforeNamespace($tokens, $index, 2, 2);
            }
        }
    }
}
