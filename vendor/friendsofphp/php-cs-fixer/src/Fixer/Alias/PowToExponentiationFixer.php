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
namespace MolliePrefix\PhpCsFixer\Fixer\Alias;

use MolliePrefix\PhpCsFixer\AbstractFunctionReferenceFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class PowToExponentiationFixer extends \MolliePrefix\PhpCsFixer\AbstractFunctionReferenceFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        // minimal candidate to fix is seven tokens: pow(x,y);
        return $tokens->count() > 7 && $tokens->isTokenKindFound(\T_STRING);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Converts `pow` to the `**` operator.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n pow(\$a, 1);\n")], null, 'Risky when the function `pow` is overridden.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer, MethodArgumentSpaceFixer, NativeFunctionCasingFixer, NoSpacesAfterFunctionNameFixer, NoSpacesInsideParenthesisFixer.
     */
    public function getPriority()
    {
        return 3;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $candidates = $this->findPowCalls($tokens);
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        $numberOfTokensAdded = 0;
        $previousCloseParenthesisIndex = \count($tokens);
        foreach (\array_reverse($candidates) as $candidate) {
            // if in the previous iteration(s) tokens were added to the collection and this is done within the tokens
            // indexes of the current candidate than the index of the close ')' of the candidate has moved and so
            // the index needs to be updated
            if ($previousCloseParenthesisIndex < $candidate[2]) {
                $previousCloseParenthesisIndex = $candidate[2];
                $candidate[2] += $numberOfTokensAdded;
            } else {
                $previousCloseParenthesisIndex = $candidate[2];
                $numberOfTokensAdded = 0;
            }
            $arguments = $argumentsAnalyzer->getArguments($tokens, $candidate[1], $candidate[2]);
            if (2 !== \count($arguments)) {
                continue;
            }
            for ($i = $candidate[1]; $i < $candidate[2]; ++$i) {
                if ($tokens[$i]->isGivenKind(\T_ELLIPSIS)) {
                    continue 2;
                }
            }
            $numberOfTokensAdded += $this->fixPowToExponentiation(
                $tokens,
                $candidate[0],
                // functionNameIndex,
                $candidate[1],
                // openParenthesisIndex,
                $candidate[2],
                // closeParenthesisIndex,
                $arguments
            );
        }
    }
    /**
     * @return array[]
     */
    private function findPowCalls(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $candidates = [];
        // Minimal candidate to fix is seven tokens: pow(x,y);
        $end = \count($tokens) - 6;
        // First possible location is after the open token: 1
        for ($i = 1; $i < $end; ++$i) {
            $candidate = $this->find('pow', $tokens, $i, $end);
            if (null === $candidate) {
                break;
            }
            $i = $candidate[1];
            // proceed to openParenthesisIndex
            $candidates[] = $candidate;
        }
        return $candidates;
    }
    /**
     * @param int            $functionNameIndex
     * @param int            $openParenthesisIndex
     * @param int            $closeParenthesisIndex
     * @param array<int,int> $arguments
     *
     * @return int number of tokens added to the collection
     */
    private function fixPowToExponentiation(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $functionNameIndex, $openParenthesisIndex, $closeParenthesisIndex, array $arguments)
    {
        // find the argument separator ',' directly after the last token of the first argument;
        // replace it with T_POW '**'
        $tokens[$tokens->getNextTokenOfKind(\reset($arguments), [','])] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_POW, '**']);
        // clean up the function call tokens prt. I
        $tokens->clearAt($closeParenthesisIndex);
        $previousIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);
        if ($tokens[$previousIndex]->equals(',')) {
            $tokens->clearAt($previousIndex);
            // trailing ',' in function call (PHP 7.3)
        }
        $added = 0;
        // check if the arguments need to be wrapped in parenthesis
        foreach (\array_reverse($arguments, \true) as $argumentStartIndex => $argumentEndIndex) {
            if ($this->isParenthesisNeeded($tokens, $argumentStartIndex, $argumentEndIndex)) {
                $tokens->insertAt($argumentEndIndex + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token(')'));
                $tokens->insertAt($argumentStartIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token('('));
                $added += 2;
            }
        }
        // clean up the function call tokens prt. II
        $tokens->clearAt($openParenthesisIndex);
        $tokens->clearAt($functionNameIndex);
        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($functionNameIndex);
        if ($tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $tokens->clearAt($prevMeaningfulTokenIndex);
        }
        return $added;
    }
    /**
     * @param int $argumentStartIndex
     * @param int $argumentEndIndex
     *
     * @return bool
     */
    private function isParenthesisNeeded(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $argumentStartIndex, $argumentEndIndex)
    {
        static $allowedKinds = [\T_DNUMBER, \T_LNUMBER, \T_VARIABLE, \T_STRING, \T_OBJECT_OPERATOR, \T_CONSTANT_ENCAPSED_STRING, \T_DOUBLE_CAST, \T_INT_CAST, \T_INC, \T_DEC, \T_NS_SEPARATOR, \T_WHITESPACE, \T_DOUBLE_COLON, \T_LINE, \T_COMMENT, \T_DOC_COMMENT, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NAMESPACE_OPERATOR];
        for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
            if ($tokens[$i]->isGivenKind($allowedKinds) || $tokens->isEmptyAt($i)) {
                continue;
            }
            $blockType = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::detectBlockType($tokens[$i]);
            if (null !== $blockType) {
                $i = $tokens->findBlockEnd($blockType['type'], $i);
                continue;
            }
            if ($tokens[$i]->equals('$')) {
                $i = $tokens->getNextMeaningfulToken($i);
                if ($tokens[$i]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_VAR_BRACE_OPEN)) {
                    $i = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_DYNAMIC_VAR_BRACE, $i);
                    continue;
                }
            }
            if ($tokens[$i]->equals('+') && $tokens->getPrevMeaningfulToken($i) < $argumentStartIndex) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
