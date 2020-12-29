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
namespace MolliePrefix\PhpCsFixer\Fixer\FunctionNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
final class NoUselessSprintfFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There must be no `sprintf` calls with only the first argument.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$foo = sprintf('bar');\n")], null, 'Risky when if the `sprintf` function is overridden.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     *
     * Must run before MethodArgumentSpaceFixer, NativeFunctionCasingFixer, NoEmptyStatementFixer, NoExtraBlankLinesFixer, NoSpacesInsideParenthesisFixer.
     */
    public function getPriority()
    {
        return 27;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $functionAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }
            if ('sprintf' !== \strtolower($tokens[$index]->getContent())) {
                continue;
            }
            if (!$functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }
            $openParenthesisIndex = $tokens->getNextTokenOfKind($index, ['(']);
            if ($tokens[$tokens->getNextMeaningfulToken($openParenthesisIndex)]->isGivenKind(\T_ELLIPSIS)) {
                continue;
            }
            $closeParenthesisIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);
            if (1 !== $argumentsAnalyzer->countArguments($tokens, $openParenthesisIndex, $closeParenthesisIndex)) {
                continue;
            }
            $tokens->clearTokenAndMergeSurroundingWhitespace($closeParenthesisIndex);
            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);
            if ($tokens[$prevMeaningfulTokenIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($prevMeaningfulTokenIndex);
            }
            $tokens->clearTokenAndMergeSurroundingWhitespace($openParenthesisIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($prevMeaningfulTokenIndex);
            }
        }
    }
}
