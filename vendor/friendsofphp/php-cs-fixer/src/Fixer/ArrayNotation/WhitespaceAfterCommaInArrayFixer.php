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
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Adam Marczuk <adam@marczuk.info>
 */
final class WhitespaceAfterCommaInArrayFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('In array declaration, there MUST be a whitespace after each comma.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$sample = array(1,'a',\$b,);\n")]);
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
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if ($tokens[$index]->isGivenKind([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                $this->fixSpacing($index, $tokens);
            }
        }
    }
    /**
     * Method to fix spacing in array declaration.
     *
     * @param int $index
     */
    private function fixSpacing($index, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ($tokens[$index]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            $startIndex = $index;
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $startIndex);
        } else {
            $startIndex = $tokens->getNextTokenOfKind($index, ['(']);
            $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startIndex);
        }
        for ($i = $endIndex - 1; $i > $startIndex; --$i) {
            $i = $this->skipNonArrayElements($i, $tokens);
            if ($tokens[$i]->equals(',') && !$tokens[$i + 1]->isWhitespace()) {
                $tokens->insertAt($i + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
        }
    }
    /**
     * Method to move index over the non-array elements like function calls or function declarations.
     *
     * @param int $index
     *
     * @return int New index
     */
    private function skipNonArrayElements($index, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ($tokens[$index]->equals('}')) {
            return $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
        }
        if ($tokens[$index]->equals(')')) {
            $startIndex = $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
            $startIndex = $tokens->getPrevMeaningfulToken($startIndex);
            if (!$tokens[$startIndex]->isGivenKind([\T_ARRAY, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                return $startIndex;
            }
        }
        return $index;
    }
}
