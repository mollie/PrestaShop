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

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author ntzm
 */
final class FinalStaticAccessFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Converts `static` access to `self` access in `final` classes.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Sample
{
    public function getFoo()
    {
        return static::class;
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after FinalInternalClassFixer, PhpUnitTestCaseStaticMethodCallsFixer.
     */
    public function getPriority()
    {
        return -1;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_FINAL, \T_CLASS, \T_STATIC]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_FINAL)) {
                continue;
            }
            $classTokenIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$classTokenIndex]->isGivenKind(\T_CLASS)) {
                continue;
            }
            $startClassIndex = $tokens->getNextTokenOfKind($classTokenIndex, ['{']);
            $endClassIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $startClassIndex);
            $this->replaceStaticAccessWithSelfAccessBetween($tokens, $startClassIndex, $endClassIndex);
        }
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function replaceStaticAccessWithSelfAccessBetween(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        for ($index = $startIndex; $index <= $endIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_CLASS)) {
                $index = $this->getEndOfAnonymousClass($tokens, $index);
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_STATIC)) {
                continue;
            }
            $doubleColonIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$doubleColonIndex]->isGivenKind(\T_DOUBLE_COLON)) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'self']);
        }
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function getEndOfAnonymousClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $instantiationBraceStart = $tokens->getNextMeaningfulToken($index);
        if ($tokens[$instantiationBraceStart]->equals('(')) {
            $index = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $instantiationBraceStart);
        }
        $bodyBraceStart = $tokens->getNextTokenOfKind($index, ['{']);
        return $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $bodyBraceStart);
    }
}
