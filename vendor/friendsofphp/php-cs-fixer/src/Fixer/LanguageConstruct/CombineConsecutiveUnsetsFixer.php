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
namespace MolliePrefix\PhpCsFixer\Fixer\LanguageConstruct;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class CombineConsecutiveUnsetsFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Calling `unset` on multiple items should be done in one call.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nunset(\$a); unset(\$b);\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer, NoWhitespaceInBlankLineFixer, SpaceAfterSemicolonFixer.
     * Must run after NoEmptyStatementFixer, NoUnsetOnPropertyFixer, NoUselessElseFixer.
     */
    public function getPriority()
    {
        return 24;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_UNSET);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_UNSET)) {
                continue;
            }
            $previousUnsetCall = $this->getPreviousUnsetCall($tokens, $index);
            if (\is_int($previousUnsetCall)) {
                $index = $previousUnsetCall;
                continue;
            }
            list($previousUnset, , $previousUnsetBraceEnd) = $previousUnsetCall;
            // Merge the tokens inside the 'unset' call into the previous one 'unset' call.
            $tokensAddCount = $this->moveTokens($tokens, $nextUnsetContentStart = $tokens->getNextTokenOfKind($index, ['(']), $nextUnsetContentEnd = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextUnsetContentStart), $previousUnsetBraceEnd - 1);
            if (!$tokens[$previousUnsetBraceEnd]->isWhitespace()) {
                $tokens->insertAt($previousUnsetBraceEnd, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
                ++$tokensAddCount;
            }
            $tokens->insertAt($previousUnsetBraceEnd, new \MolliePrefix\PhpCsFixer\Tokenizer\Token(','));
            ++$tokensAddCount;
            // Remove 'unset', '(', ')' and (possibly) ';' from the merged 'unset' call.
            $this->clearOffsetTokens($tokens, $tokensAddCount, [$index, $nextUnsetContentStart, $nextUnsetContentEnd]);
            $nextUnsetSemicolon = $tokens->getNextMeaningfulToken($nextUnsetContentEnd);
            if (null !== $nextUnsetSemicolon && $tokens[$nextUnsetSemicolon]->equals(';')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($nextUnsetSemicolon);
            }
            $index = $previousUnset + 1;
        }
    }
    /**
     * @param int   $offset
     * @param int[] $indices
     */
    private function clearOffsetTokens(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $offset, array $indices)
    {
        foreach ($indices as $index) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index + $offset);
        }
    }
    /**
     * Find a previous call to unset directly before the index.
     *
     * Returns an array with
     * * unset index
     * * opening brace index
     * * closing brace index
     * * end semicolon index
     *
     * Or the index to where the method looked for an call.
     *
     * @param int $index
     *
     * @return int|int[]
     */
    private function getPreviousUnsetCall(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $previousUnsetSemicolon = $tokens->getPrevMeaningfulToken($index);
        if (null === $previousUnsetSemicolon) {
            return $index;
        }
        if (!$tokens[$previousUnsetSemicolon]->equals(';')) {
            return $previousUnsetSemicolon;
        }
        $previousUnsetBraceEnd = $tokens->getPrevMeaningfulToken($previousUnsetSemicolon);
        if (null === $previousUnsetBraceEnd) {
            return $index;
        }
        if (!$tokens[$previousUnsetBraceEnd]->equals(')')) {
            return $previousUnsetBraceEnd;
        }
        $previousUnsetBraceStart = $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $previousUnsetBraceEnd);
        $previousUnset = $tokens->getPrevMeaningfulToken($previousUnsetBraceStart);
        if (null === $previousUnset) {
            return $index;
        }
        if (!$tokens[$previousUnset]->isGivenKind(\T_UNSET)) {
            return $previousUnset;
        }
        return [$previousUnset, $previousUnsetBraceStart, $previousUnsetBraceEnd, $previousUnsetSemicolon];
    }
    /**
     * @param int $start Index previous of the first token to move
     * @param int $end   Index of the last token to move
     * @param int $to    Upper boundary index
     *
     * @return int Number of tokens inserted
     */
    private function moveTokens(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end, $to)
    {
        $added = 0;
        for ($i = $start + 1; $i < $end; $i += 2) {
            if ($tokens[$i]->isWhitespace() && $tokens[$to + 1]->isWhitespace()) {
                $tokens[$to + 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $tokens[$to + 1]->getContent() . $tokens[$i]->getContent()]);
            } else {
                $tokens->insertAt(++$to, clone $tokens[$i]);
                ++$end;
                ++$added;
            }
            $tokens->clearAt($i + 1);
        }
        return $added;
    }
}
