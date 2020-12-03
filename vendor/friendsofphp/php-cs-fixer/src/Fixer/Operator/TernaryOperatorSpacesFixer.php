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
namespace MolliePrefix\PhpCsFixer\Fixer\Operator;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\CaseAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\SwitchAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class TernaryOperatorSpacesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Standardize spaces around ternary operator.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$a = \$a   ?1 :0;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after ArraySyntaxFixer, ListSyntaxFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound(['?', ':']);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $ternaryOperatorIndices = [];
        $excludedIndices = [];
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(\T_SWITCH)) {
                $excludedIndices = \array_merge($excludedIndices, $this->getColonIndicesForSwitch($tokens, $index));
                continue;
            }
            if (!$token->equalsAny(['?', ':'])) {
                continue;
            }
            if (\in_array($index, $excludedIndices, \true)) {
                continue;
            }
            if ($this->belongsToGoToLabel($tokens, $index)) {
                continue;
            }
            $ternaryOperatorIndices[] = $index;
        }
        foreach (\array_reverse($ternaryOperatorIndices) as $index) {
            $token = $tokens[$index];
            if ($token->equals('?')) {
                $nextNonWhitespaceIndex = $tokens->getNextNonWhitespace($index);
                if ($tokens[$nextNonWhitespaceIndex]->equals(':')) {
                    // for `$a ?: $b` remove spaces between `?` and `:`
                    $tokens->ensureWhitespaceAtIndex($index + 1, 0, '');
                } else {
                    // for `$a ? $b : $c` ensure space after `?`
                    $this->ensureWhitespaceExistence($tokens, $index + 1, \true);
                }
                // for `$a ? $b : $c` ensure space before `?`
                $this->ensureWhitespaceExistence($tokens, $index - 1, \false);
                continue;
            }
            if ($token->equals(':')) {
                // for `$a ? $b : $c` ensure space after `:`
                $this->ensureWhitespaceExistence($tokens, $index + 1, \true);
                $prevNonWhitespaceToken = $tokens[$tokens->getPrevNonWhitespace($index)];
                if (!$prevNonWhitespaceToken->equals('?')) {
                    // for `$a ? $b : $c` ensure space before `:`
                    $this->ensureWhitespaceExistence($tokens, $index - 1, \false);
                }
            }
        }
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function belongsToGoToLabel(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index]->equals(':')) {
            return \false;
        }
        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_STRING)) {
            return \false;
        }
        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($prevMeaningfulTokenIndex);
        return $tokens[$prevMeaningfulTokenIndex]->equalsAny([';', '{', '}', [\T_OPEN_TAG]]);
    }
    /**
     * @param int $switchIndex
     *
     * @return int[]
     */
    private function getColonIndicesForSwitch(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $switchIndex)
    {
        return \array_map(static function (\MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\CaseAnalysis $caseAnalysis) {
            return $caseAnalysis->getColonIndex();
        }, (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\SwitchAnalyzer())->getSwitchAnalysis($tokens, $switchIndex)->getCases());
    }
    /**
     * @param int  $index
     * @param bool $after
     */
    private function ensureWhitespaceExistence(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $after)
    {
        if ($tokens[$index]->isWhitespace()) {
            if (\false === \strpos($tokens[$index]->getContent(), "\n") && !$tokens[$index - 1]->isComment()) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            }
            return;
        }
        $index += $after ? 0 : 1;
        $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
    }
}
