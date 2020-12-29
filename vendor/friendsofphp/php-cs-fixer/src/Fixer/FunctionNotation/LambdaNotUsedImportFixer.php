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
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author SpacePossum
 */
final class LambdaNotUsedImportFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * @var ArgumentsAnalyzer
     */
    private $argumentsAnalyzer;
    /**
     * @var FunctionsAnalyzer
     */
    private $functionAnalyzer;
    /**
     * @var TokensAnalyzer
     */
    private $tokensAnalyzer;
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Lambda must not import variables it doesn\'t use.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$foo = function() use (\$bar) {};\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoSpacesInsideParenthesisFixer.
     */
    public function getPriority()
    {
        return 3;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_FUNCTION, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA]);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $this->argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        $this->functionAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        $this->tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $tokens->count() - 4; $index > 0; --$index) {
            $lambdaUseIndex = $this->getLambdaUseIndex($tokens, $index);
            if (\false !== $lambdaUseIndex) {
                $this->fixLambda($tokens, $lambdaUseIndex);
            }
        }
    }
    /**
     * @param int $lambdaUseIndex
     */
    private function fixLambda(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $lambdaUseIndex)
    {
        $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($lambdaUseIndex, ['(']);
        $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $lambdaUseOpenBraceIndex);
        $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);
        $imports = $this->filterArguments($tokens, $arguments);
        if (0 === \count($imports)) {
            return;
            // no imports to remove
        }
        $notUsedImports = $this->findNotUsedLambdaImports($tokens, $imports, $lambdaUseCloseBraceIndex);
        $notUsedImportsCount = \count($notUsedImports);
        if (0 === $notUsedImportsCount) {
            return;
            // no not used imports found
        }
        if ($notUsedImportsCount === \count($arguments)) {
            $this->clearImportsAndUse($tokens, $lambdaUseIndex, $lambdaUseCloseBraceIndex);
            // all imports are not used
            return;
        }
        $this->clearImports($tokens, \array_reverse($notUsedImports));
    }
    /**
     * @param int $lambdaUseCloseBraceIndex
     *
     * @return array
     */
    private function findNotUsedLambdaImports(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $imports, $lambdaUseCloseBraceIndex)
    {
        static $riskyKinds = [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_VAR_BRACE_OPEN, \T_EVAL, \T_INCLUDE, \T_INCLUDE_ONCE, \T_REQUIRE, \T_REQUIRE_ONCE];
        // figure out where the lambda starts ...
        $lambdaOpenIndex = $tokens->getNextTokenOfKind($lambdaUseCloseBraceIndex, ['{']);
        $curlyBracesLevel = 0;
        for ($index = $lambdaOpenIndex;; ++$index) {
            // go through the body of the lambda and keep count of the (possible) usages of the imported variables
            $token = $tokens[$index];
            if ($token->equals('{')) {
                ++$curlyBracesLevel;
                continue;
            }
            if ($token->equals('}')) {
                --$curlyBracesLevel;
                if (0 === $curlyBracesLevel) {
                    break;
                }
                continue;
            }
            if ($token->isGivenKind(\T_STRING) && 'compact' === \strtolower($token->getContent()) && $this->functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                return [];
                // wouldn't touch it with a ten-foot pole
            }
            if ($token->isGivenKind($riskyKinds)) {
                return [];
            }
            if ($token->equals('$')) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$nextIndex]->isGivenKind(\T_VARIABLE)) {
                    return [];
                    // "$$a" case
                }
            }
            if ($token->isGivenKind(\T_VARIABLE)) {
                $content = $token->getContent();
                if (isset($imports[$content])) {
                    unset($imports[$content]);
                    if (0 === \count($imports)) {
                        return $imports;
                    }
                }
            }
            if ($token->isGivenKind(\T_STRING_VARNAME)) {
                $content = '$' . $token->getContent();
                if (isset($imports[$content])) {
                    unset($imports[$content]);
                    if (0 === \count($imports)) {
                        return $imports;
                    }
                }
            }
            if ($token->isClassy()) {
                // is anonymous class
                // check if used as argument in the constructor of the anonymous class
                $index = $tokens->getNextTokenOfKind($index, ['(', '{']);
                if ($tokens[$index]->equals('(')) {
                    $closeBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
                    $arguments = $this->argumentsAnalyzer->getArguments($tokens, $index, $closeBraceIndex);
                    $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);
                    $index = $tokens->getNextTokenOfKind($closeBraceIndex, ['{']);
                }
                // skip body
                $index = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }
            if ($token->isGivenKind(\T_FUNCTION)) {
                // check if used as argument
                $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
                $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $lambdaUseOpenBraceIndex);
                $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);
                $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);
                // check if used as import
                $index = $tokens->getNextTokenOfKind($index, [[\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA], '{']);
                if ($tokens[$index]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA)) {
                    $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
                    $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $lambdaUseOpenBraceIndex);
                    $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);
                    $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);
                    $index = $tokens->getNextTokenOfKind($lambdaUseCloseBraceIndex, ['{']);
                }
                // skip body
                $index = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }
        }
        return $imports;
    }
    /**
     * @return array
     */
    private function countImportsUsedAsArgument(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $imports, array $arguments)
    {
        foreach ($arguments as $start => $end) {
            $info = $this->argumentsAnalyzer->getArgumentInfo($tokens, $start, $end);
            $content = $info->getName();
            if (isset($imports[$content])) {
                unset($imports[$content]);
                if (0 === \count($imports)) {
                    return $imports;
                }
            }
        }
        return $imports;
    }
    /**
     * @param int $index
     *
     * @return false|int
     */
    private function getLambdaUseIndex(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index]->isGivenKind(\T_FUNCTION) || !$this->tokensAnalyzer->isLambda($index)) {
            return \false;
        }
        $lambdaUseIndex = $tokens->getNextMeaningfulToken($index);
        // we are @ '(' or '&' after this
        if ($tokens[$lambdaUseIndex]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_RETURN_REF)) {
            $lambdaUseIndex = $tokens->getNextMeaningfulToken($lambdaUseIndex);
        }
        $lambdaUseIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $lambdaUseIndex);
        // we are @ ')' after this
        $lambdaUseIndex = $tokens->getNextMeaningfulToken($lambdaUseIndex);
        if (!$tokens[$lambdaUseIndex]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA)) {
            return \false;
        }
        return $lambdaUseIndex;
    }
    /**
     * @return array
     */
    private function filterArguments(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $arguments)
    {
        $imports = [];
        foreach ($arguments as $start => $end) {
            $info = $this->argumentsAnalyzer->getArgumentInfo($tokens, $start, $end);
            $argument = $info->getNameIndex();
            if ($tokens[$tokens->getPrevMeaningfulToken($argument)]->equals('&')) {
                continue;
            }
            $argumentCandidate = $tokens[$argument];
            if ('$this' === $argumentCandidate->getContent()) {
                continue;
            }
            if ($this->tokensAnalyzer->isSuperGlobal($argument)) {
                continue;
            }
            $imports[$argumentCandidate->getContent()] = $argument;
        }
        return $imports;
    }
    private function clearImports(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $imports)
    {
        foreach ($imports as $content => $removeIndex) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($removeIndex);
            $previousRemoveIndex = $tokens->getPrevMeaningfulToken($removeIndex);
            if ($tokens[$previousRemoveIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($previousRemoveIndex);
            } elseif ($tokens[$previousRemoveIndex]->equals('(')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($tokens->getNextMeaningfulToken($removeIndex));
                // next is always ',' here
            }
        }
    }
    /**
     * Remove `use` and all imported variables.
     *
     * @param int $lambdaUseIndex
     * @param int $lambdaUseCloseBraceIndex
     */
    private function clearImportsAndUse(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $lambdaUseIndex, $lambdaUseCloseBraceIndex)
    {
        for ($i = $lambdaUseCloseBraceIndex; $i >= $lambdaUseIndex; --$i) {
            if ($tokens[$i]->isComment()) {
                continue;
            }
            if ($tokens[$i]->isWhitespace()) {
                $previousIndex = $tokens->getPrevNonWhitespace($i);
                if ($tokens[$previousIndex]->isComment()) {
                    continue;
                }
            }
            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }
    }
}
