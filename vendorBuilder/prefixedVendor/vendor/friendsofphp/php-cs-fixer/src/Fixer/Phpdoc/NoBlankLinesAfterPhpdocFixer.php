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
namespace MolliePrefix\PhpCsFixer\Fixer\Phpdoc;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 */
final class NoBlankLinesAfterPhpdocFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There should not be blank lines between docblock and the documented element.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

/**
 * This is the bar class.
 */


class Bar {}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before HeaderCommentFixer, PhpdocAlignFixer, SingleBlankLineBeforeNamespaceFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return -20;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        static $forbiddenSuccessors = [\T_DOC_COMMENT, \T_COMMENT, \T_WHITESPACE, \T_RETURN, \T_THROW, \T_GOTO, \T_CONTINUE, \T_BREAK, \T_DECLARE, \T_USE];
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            // get the next non-whitespace token inc comments, provided
            // that there is whitespace between it and the current token
            $next = $tokens->getNextNonWhitespace($index);
            if ($index + 2 === $next && \false === $tokens[$next]->isGivenKind($forbiddenSuccessors)) {
                $this->fixWhitespace($tokens, $index + 1);
            }
        }
    }
    /**
     * Cleanup a whitespace token.
     *
     * @param int $index
     */
    private function fixWhitespace(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $content = $tokens[$index]->getContent();
        // if there is more than one new line in the whitespace, then we need to fix it
        if (\substr_count($content, "\n") > 1) {
            // the final bit of the whitespace must be the next statement's indentation
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, \substr($content, \strrpos($content, "\n"))]);
        }
    }
}
