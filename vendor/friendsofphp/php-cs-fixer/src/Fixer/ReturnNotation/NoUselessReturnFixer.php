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
namespace MolliePrefix\PhpCsFixer\Fixer\ReturnNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class NoUselessReturnFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_FUNCTION, \T_RETURN]);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There should not be an empty `return` statement at the end of a function.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
function example($b) {
    if ($b) {
        return;
    }
    return;
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BlankLineBeforeReturnFixer, BlankLineBeforeStatementFixer, NoExtraBlankLinesFixer, NoWhitespaceInBlankLineFixer.
     * Must run after NoEmptyStatementFixer, NoUnneededCurlyBracesFixer, NoUselessElseFixer, SimplifiedNullReturnFixer.
     */
    public function getPriority()
    {
        return -18;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_FUNCTION)) {
                continue;
            }
            $index = $tokens->getNextTokenOfKind($index, [';', '{']);
            if ($tokens[$index]->equals('{')) {
                $this->fixFunction($tokens, $index, $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index));
            }
        }
    }
    /**
     * @param int $start Token index of the opening brace token of the function
     * @param int $end   Token index of the closing brace token of the function
     */
    private function fixFunction(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end)
    {
        for ($index = $end; $index > $start; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_RETURN)) {
                continue;
            }
            $nextAt = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$nextAt]->equals(';')) {
                continue;
            }
            if ($tokens->getNextMeaningfulToken($nextAt) !== $end) {
                continue;
            }
            $previous = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$previous]->equalsAny([[\T_ELSE], ')'])) {
                continue;
            }
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            $tokens->clearTokenAndMergeSurroundingWhitespace($nextAt);
        }
    }
}
