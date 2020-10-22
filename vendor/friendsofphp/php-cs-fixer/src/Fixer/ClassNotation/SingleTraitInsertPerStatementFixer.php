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
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class SingleTraitInsertPerStatementFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Each trait `use` must be done as single statement.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Example
{
    use Foo, Bar;
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer, SpaceAfterSemicolonFixer.
     */
    public function getPriority()
    {
        return 1;
    }
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = \count($tokens) - 1; 1 < $index; --$index) {
            if ($tokens[$index]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT)) {
                $candidates = $this->getCandidates($tokens, $index);
                if (\count($candidates) > 0) {
                    $this->fixTraitUse($tokens, $index, $candidates);
                }
            }
        }
    }
    /**
     * @param int   $useTraitIndex
     * @param int[] $candidates    ',' indexes to fix
     */
    private function fixTraitUse(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $useTraitIndex, array $candidates)
    {
        foreach ($candidates as $commaIndex) {
            $inserts = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT, 'use']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])];
            $nextImportStartIndex = $tokens->getNextMeaningfulToken($commaIndex);
            if ($tokens[$nextImportStartIndex - 1]->isWhitespace()) {
                if (1 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $tokens[$nextImportStartIndex - 1]->getContent())) {
                    \array_unshift($inserts, clone $tokens[$useTraitIndex - 1]);
                }
                $tokens->clearAt($nextImportStartIndex - 1);
            }
            $tokens[$commaIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';');
            $tokens->insertAt($nextImportStartIndex, $inserts);
        }
    }
    /**
     * @param int $index
     *
     * @return int[]
     */
    private function getCandidates(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $indexes = [];
        $index = $tokens->getNextTokenOfKind($index, [',', ';', '{']);
        while (!$tokens[$index]->equals(';')) {
            if ($tokens[$index]->equals('{')) {
                return [];
                // do not fix use cases with grouping
            }
            $indexes[] = $index;
            $index = $tokens->getNextTokenOfKind($index, [',', ';', '{']);
        }
        return \array_reverse($indexes);
    }
}
