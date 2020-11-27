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
namespace MolliePrefix\PhpCsFixer\Fixer\Comment;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class NoTrailingWhitespaceInCommentFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There MUST be no trailing spaces inside comment or PHPDoc.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
// This is ' . '
// a comment. ' . '
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after PhpdocNoUselessInheritdocFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(\T_DOC_COMMENT)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \MolliePrefix\PhpCsFixer\Preg::replace('/(*ANY)[\\h]+$/m', '', $token->getContent())]);
                continue;
            }
            if ($token->isGivenKind(\T_COMMENT)) {
                if ('/*' === \substr($token->getContent(), 0, 2)) {
                    $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_COMMENT, \MolliePrefix\PhpCsFixer\Preg::replace('/(*ANY)[\\h]+$/m', '', $token->getContent())]);
                } elseif (isset($tokens[$index + 1]) && $tokens[$index + 1]->isWhitespace()) {
                    $trimmedContent = \ltrim($tokens[$index + 1]->getContent(), " \t");
                    if ('' !== $trimmedContent) {
                        $tokens[$index + 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $trimmedContent]);
                    } else {
                        $tokens->clearAt($index + 1);
                    }
                }
            }
        }
    }
}
