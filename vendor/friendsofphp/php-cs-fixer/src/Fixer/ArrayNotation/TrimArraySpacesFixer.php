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
namespace MolliePrefix\PhpCsFixer\Fixer\ArrayNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Jared Henderson <jared@netrivet.com>
 */
final class TrimArraySpacesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Arrays should be formatted like function/method arguments, without leading or trailing single line space.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$sample = array( );\n\$sample = array( 'a', 'b' );\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = 0, $c = $tokens->count(); $index < $c; ++$index) {
            if ($tokens[$index]->isGivenKind([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                self::fixArray($tokens, $index);
            }
        }
    }
    /**
     * Method to trim leading/trailing whitespace within single line arrays.
     *
     * @param int $index
     */
    private static function fixArray(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $startIndex = $index;
        if ($tokens[$startIndex]->isGivenKind(\T_ARRAY)) {
            $startIndex = $tokens->getNextMeaningfulToken($startIndex);
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startIndex);
        } else {
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $startIndex);
        }
        $nextIndex = $startIndex + 1;
        $nextToken = $tokens[$nextIndex];
        $nextNonWhitespaceIndex = $tokens->getNextNonWhitespace($startIndex);
        $nextNonWhitespaceToken = $tokens[$nextNonWhitespaceIndex];
        $tokenAfterNextNonWhitespaceToken = $tokens[$nextNonWhitespaceIndex + 1];
        $prevIndex = $endIndex - 1;
        $prevToken = $tokens[$prevIndex];
        $prevNonWhitespaceIndex = $tokens->getPrevNonWhitespace($endIndex);
        $prevNonWhitespaceToken = $tokens[$prevNonWhitespaceIndex];
        if ($nextToken->isWhitespace(" \t") && (!$nextNonWhitespaceToken->isComment() || $nextNonWhitespaceIndex === $prevNonWhitespaceIndex || $tokenAfterNextNonWhitespaceToken->isWhitespace(" \t") || '/*' === \substr($nextNonWhitespaceToken->getContent(), 0, 2))) {
            $tokens->clearAt($nextIndex);
        }
        if ($prevToken->isWhitespace(" \t") && !$prevNonWhitespaceToken->equals(',')) {
            $tokens->clearAt($prevIndex);
        }
    }
}
