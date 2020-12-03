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
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 * @author SpacePossum
 */
final class ProtectedToPrivateFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Converts `protected` variables and methods to `private` where possible.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Sample
{
    protected $a;

    protected function test()
    {
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before OrderedClassElementsFixer.
     * Must run after FinalInternalClassFixer.
     */
    public function getPriority()
    {
        return 66;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_FINAL, \T_PROTECTED]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $end = \count($tokens) - 3;
        // min. number of tokens to form a class candidate to fix
        for ($index = 0; $index < $end; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }
            $classOpen = $tokens->getNextTokenOfKind($index, ['{']);
            $classClose = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpen);
            if (!$this->skipClass($tokens, $index, $classOpen, $classClose)) {
                $this->fixClass($tokens, $classOpen, $classClose);
            }
            $index = $classClose;
        }
    }
    /**
     * @param int $classOpenIndex
     * @param int $classCloseIndex
     */
    private function fixClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $classOpenIndex, $classCloseIndex)
    {
        for ($index = $classOpenIndex + 1; $index < $classCloseIndex; ++$index) {
            if ($tokens[$index]->equals('{')) {
                $index = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_PROTECTED)) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PRIVATE, 'private']);
        }
    }
    /**
     * Decide whether or not skip the fix for given class.
     *
     * @param int $classIndex
     * @param int $classOpenIndex
     * @param int $classCloseIndex
     *
     * @return bool
     */
    private function skipClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $classIndex, $classOpenIndex, $classCloseIndex)
    {
        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($classIndex)];
        if (!$prevToken->isGivenKind(\T_FINAL)) {
            return \true;
        }
        for ($index = $classIndex; $index < $classOpenIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_EXTENDS)) {
                return \true;
            }
        }
        $useIndex = $tokens->getNextTokenOfKind($classIndex, [[\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT]]);
        return $useIndex && $useIndex < $classCloseIndex;
    }
}
