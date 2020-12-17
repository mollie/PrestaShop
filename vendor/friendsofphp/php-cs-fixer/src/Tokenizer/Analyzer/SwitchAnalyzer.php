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
namespace MolliePrefix\PhpCsFixer\Tokenizer\Analyzer;

use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\CaseAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @internal
 */
final class SwitchAnalyzer
{
    /**
     * @param int $switchIndex
     *
     * @return SwitchAnalysis
     */
    public function getSwitchAnalysis(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $switchIndex)
    {
        if (!$tokens[$switchIndex]->isGivenKind(\T_SWITCH)) {
            throw new \InvalidArgumentException(\sprintf('Index %d is not "switch".', $switchIndex));
        }
        $casesStartIndex = $this->getCasesStart($tokens, $switchIndex);
        $casesEndIndex = $this->getCasesEnd($tokens, $casesStartIndex);
        $cases = [];
        $index = $casesStartIndex;
        while ($index < $casesEndIndex) {
            $index = $this->getNextSameLevelToken($tokens, $index);
            if (!$tokens[$index]->isGivenKind([\T_CASE, \T_DEFAULT])) {
                continue;
            }
            $caseAnalysis = $this->getCaseAnalysis($tokens, $index);
            $cases[] = $caseAnalysis;
        }
        return new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis($casesStartIndex, $casesEndIndex, $cases);
    }
    /**
     * @param int $switchIndex
     *
     * @return int
     */
    private function getCasesStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $switchIndex)
    {
        /** @var int $parenthesisStartIndex */
        $parenthesisStartIndex = $tokens->getNextMeaningfulToken($switchIndex);
        $parenthesisEndIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $parenthesisStartIndex);
        $casesStartIndex = $tokens->getNextMeaningfulToken($parenthesisEndIndex);
        \assert(\is_int($casesStartIndex));
        return $casesStartIndex;
    }
    /**
     * @param int $casesStartIndex
     *
     * @return int
     */
    private function getCasesEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $casesStartIndex)
    {
        if ($tokens[$casesStartIndex]->equals('{')) {
            return $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $casesStartIndex);
        }
        $index = $casesStartIndex;
        while ($index < $tokens->count()) {
            $index = $this->getNextSameLevelToken($tokens, $index);
            if ($tokens[$index]->isGivenKind(\T_ENDSWITCH)) {
                break;
            }
        }
        $afterEndswitchIndex = $tokens->getNextMeaningfulToken($index);
        $afterEndswitchToken = $tokens[$afterEndswitchIndex];
        return $afterEndswitchToken->equalsAny([';', [\T_CLOSE_TAG]]) ? $afterEndswitchIndex : $index;
    }
    /**
     * @param int $index
     *
     * @return CaseAnalysis
     */
    private function getCaseAnalysis(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        while ($index < $tokens->count()) {
            $index = $this->getNextSameLevelToken($tokens, $index);
            if ($tokens[$index]->equalsAny([':', ';'])) {
                break;
            }
        }
        return new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\CaseAnalysis($index);
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function getNextSameLevelToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $index = $tokens->getNextMeaningfulToken($index);
        if ($tokens[$index]->isGivenKind(\T_SWITCH)) {
            return (new self())->getSwitchAnalysis($tokens, $index)->getCasesEnd();
        }
        /** @var null|array{isStart: bool, type: int} $blockType */
        $blockType = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::detectBlockType($tokens[$index]);
        if (null !== $blockType && $blockType['isStart']) {
            return $tokens->findBlockEnd($blockType['type'], $index) + 1;
        }
        return $index;
    }
}
