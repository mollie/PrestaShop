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
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Gregor Harlan <gharlan@web.de>
 */
final class SelfAccessorFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Inside class or interface element `self` should be preferred to the class name itself.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class Sample
{
    const BAZ = 1;
    const BAR = Sample::BAZ;

    public function getBar()
    {
        return Sample::BAR;
    }
}
')], null, 'Risky when using dynamic calls like get_called_class() or late static binding.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_INTERFACE]);
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
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        foreach ((new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer())->getDeclarations($tokens) as $namespace) {
            for ($index = $namespace->getScopeStartIndex(); $index < $namespace->getScopeEndIndex(); ++$index) {
                if (!$tokens[$index]->isGivenKind([\T_CLASS, \T_INTERFACE]) || $tokensAnalyzer->isAnonymousClass($index)) {
                    continue;
                }
                $nameIndex = $tokens->getNextTokenOfKind($index, [[\T_STRING]]);
                $startIndex = $tokens->getNextTokenOfKind($nameIndex, ['{']);
                $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $startIndex);
                $name = $tokens[$nameIndex]->getContent();
                $this->replaceNameOccurrences($tokens, $namespace->getFullName(), $name, $startIndex, $endIndex);
                $index = $endIndex;
            }
        }
    }
    /**
     * Replace occurrences of the name of the classy element by "self" (if possible).
     *
     * @param string $namespace
     * @param string $name
     * @param int    $startIndex
     * @param int    $endIndex
     */
    private function replaceNameOccurrences(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $namespace, $name, $startIndex, $endIndex)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $insideMethodSignatureUntil = null;
        for ($i = $startIndex; $i < $endIndex; ++$i) {
            if ($i === $insideMethodSignatureUntil) {
                $insideMethodSignatureUntil = null;
            }
            $token = $tokens[$i];
            // skip anonymous classes
            if ($token->isGivenKind(\T_CLASS) && $tokensAnalyzer->isAnonymousClass($i)) {
                $i = $tokens->getNextTokenOfKind($i, ['{']);
                $i = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $i);
                continue;
            }
            if ($token->isGivenKind(\T_FUNCTION)) {
                $i = $tokens->getNextTokenOfKind($i, ['(']);
                $insideMethodSignatureUntil = $tokens->getNextTokenOfKind($i, ['{', ';']);
                continue;
            }
            if (!$token->equals([\T_STRING, $name], \false)) {
                continue;
            }
            $nextToken = $tokens[$tokens->getNextMeaningfulToken($i)];
            if ($nextToken->isGivenKind(\T_NS_SEPARATOR)) {
                continue;
            }
            $classStartIndex = $i;
            $prevToken = $tokens[$tokens->getPrevMeaningfulToken($i)];
            if ($prevToken->isGivenKind(\T_NS_SEPARATOR)) {
                $classStartIndex = $this->getClassStart($tokens, $i, $namespace);
                if (null === $classStartIndex) {
                    continue;
                }
                $prevToken = $tokens[$tokens->getPrevMeaningfulToken($classStartIndex)];
            }
            if ($prevToken->isGivenKind([\T_OBJECT_OPERATOR, \T_STRING])) {
                continue;
            }
            if ($prevToken->isGivenKind([\T_INSTANCEOF, \T_NEW]) || $nextToken->isGivenKind(\T_PAAMAYIM_NEKUDOTAYIM) || null !== $insideMethodSignatureUntil && $i < $insideMethodSignatureUntil && $prevToken->equalsAny(['(', ',', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_TYPE_COLON], [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE]])) {
                for ($j = $classStartIndex; $j < $i; ++$j) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($j);
                }
                $tokens[$i] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'self']);
            }
        }
    }
    private function getClassStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $namespace)
    {
        $namespace = ('' !== $namespace ? '\\' . $namespace : '') . '\\';
        foreach (\array_reverse(\MolliePrefix\PhpCsFixer\Preg::split('/(\\\\)/', $namespace, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE)) as $piece) {
            $index = $tokens->getPrevMeaningfulToken($index);
            if ('\\' === $piece) {
                if (!$tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                    return null;
                }
            } elseif (!$tokens[$index]->equals([\T_STRING, $piece], \false)) {
                return null;
            }
        }
        return $index;
    }
}
