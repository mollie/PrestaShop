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
namespace MolliePrefix\PhpCsFixer\Fixer\CastNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class NoUnsetCastFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Variables must be set `null` instead of using `(unset)` casting.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$a = (unset) \$b;\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_UNSET_CAST);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if ($tokens[$index]->isGivenKind(\T_UNSET_CAST)) {
                $this->fixUnsetCast($tokens, $index);
            }
        }
    }
    /**
     * @param int $index
     */
    private function fixUnsetCast(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $assignmentIndex = $tokens->getPrevMeaningfulToken($index);
        if (null === $assignmentIndex || !$tokens[$assignmentIndex]->equals('=')) {
            return;
        }
        $varIndex = $tokens->getNextMeaningfulToken($index);
        if (null === $varIndex || !$tokens[$varIndex]->isGivenKind(\T_VARIABLE)) {
            return;
        }
        $afterVar = $tokens->getNextMeaningfulToken($varIndex);
        if (null === $afterVar || !$tokens[$afterVar]->equalsAny([';', [\T_CLOSE_TAG]])) {
            return;
        }
        $nextIsWhiteSpace = $tokens[$assignmentIndex + 1]->isWhitespace();
        $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        $tokens->clearTokenAndMergeSurroundingWhitespace($varIndex);
        ++$assignmentIndex;
        if (!$nextIsWhiteSpace) {
            $tokens->insertAt($assignmentIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
        }
        ++$assignmentIndex;
        $tokens->insertAt($assignmentIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'null']));
    }
}
