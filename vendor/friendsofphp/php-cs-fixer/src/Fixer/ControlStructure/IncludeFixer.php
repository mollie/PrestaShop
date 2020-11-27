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
namespace MolliePrefix\PhpCsFixer\Fixer\ControlStructure;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\BlocksAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class IncludeFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Include/Require and file path should be divided with a single space. File path should not be placed under brackets.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
require ("sample1.php");
require_once  "sample2.php";
include       "sample3.php";
include_once("sample4.php");
')]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_REQUIRE, \T_REQUIRE_ONCE, \T_INCLUDE, \T_INCLUDE_ONCE]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $this->clearIncludies($tokens, $this->findIncludies($tokens));
    }
    private function clearIncludies(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $includies)
    {
        $blocksAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\BlocksAnalyzer();
        foreach ($includies as $includy) {
            if ($includy['end'] && !$tokens[$includy['end']]->isGivenKind(\T_CLOSE_TAG)) {
                $afterEndIndex = $tokens->getNextNonWhitespace($includy['end']);
                if (null === $afterEndIndex || !$tokens[$afterEndIndex]->isComment()) {
                    $tokens->removeLeadingWhitespace($includy['end']);
                }
            }
            $braces = $includy['braces'];
            if (null !== $braces) {
                $prevIndex = $tokens->getPrevMeaningfulToken($includy['begin']);
                $nextIndex = $tokens->getNextMeaningfulToken($braces['close']);
                // Include is also legal as function parameter or condition statement but requires being wrapped then.
                if (!$tokens[$nextIndex]->equalsAny([';', [\T_CLOSE_TAG]]) && !$blocksAnalyzer->isBlock($tokens, $prevIndex, $nextIndex)) {
                    continue;
                }
                $this->removeWhitespaceAroundIfPossible($tokens, $braces['open']);
                $this->removeWhitespaceAroundIfPossible($tokens, $braces['close']);
                $tokens->clearTokenAndMergeSurroundingWhitespace($braces['open']);
                $tokens->clearTokenAndMergeSurroundingWhitespace($braces['close']);
            }
            $nextIndex = $tokens->getNonEmptySibling($includy['begin'], 1);
            if ($tokens[$nextIndex]->isWhitespace()) {
                $tokens[$nextIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            } elseif (null !== $braces || $tokens[$nextIndex]->isGivenKind([\T_VARIABLE, \T_CONSTANT_ENCAPSED_STRING, \T_COMMENT])) {
                $tokens->insertAt($includy['begin'] + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
        }
    }
    private function findIncludies(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        static $includyTokenKinds = [\T_REQUIRE, \T_REQUIRE_ONCE, \T_INCLUDE, \T_INCLUDE_ONCE];
        $includies = [];
        foreach ($tokens->findGivenKind($includyTokenKinds) as $includyTokens) {
            foreach ($includyTokens as $index => $token) {
                $includy = ['begin' => $index, 'braces' => null, 'end' => $tokens->getNextTokenOfKind($index, [';', [\T_CLOSE_TAG]])];
                $braceOpenIndex = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$braceOpenIndex]->equals('(')) {
                    $braceCloseIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $braceOpenIndex);
                    $includy['braces'] = ['open' => $braceOpenIndex, 'close' => $braceCloseIndex];
                }
                $includies[$index] = $includy;
            }
        }
        \krsort($includies);
        return $includies;
    }
    /**
     * @param int $index
     */
    private function removeWhitespaceAroundIfPossible(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $nextIndex = $tokens->getNextNonWhitespace($index);
        if (null === $nextIndex || !$tokens[$nextIndex]->isComment()) {
            $tokens->removeLeadingWhitespace($index);
        }
        $prevIndex = $tokens->getPrevNonWhitespace($index);
        if (null === $prevIndex || !$tokens[$prevIndex]->isComment()) {
            $tokens->removeTrailingWhitespace($index);
        }
    }
}
