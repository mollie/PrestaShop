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
namespace MolliePrefix\PhpCsFixer\Fixer\PhpUnit;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Gert de Pagter
 */
final class PhpUnitSetUpTearDownVisibilityFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Changes the visibility of the `setUp()` and `tearDown()` functions of PHPUnit to `protected`, to match the PHPUnit TestCase.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    private $hello;
    public function setUp()
    {
        $this->hello = "hello";
    }

    public function tearDown()
    {
        $this->hello = null;
    }
}
')], null, 'This fixer may change functions named `setUp()` or `tearDown()` outside of PHPUnit tests, ' . 'when a class is wrongly seen as a PHPUnit test.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_FUNCTION]);
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
        $phpUnitTestCaseIndicator = new \MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator();
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indexes) {
            $this->fixSetUpAndTearDown($tokens, $indexes[0], $indexes[1]);
        }
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function fixSetUpAndTearDown(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $counter = 0;
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($i = $endIndex - 1; $i > $startIndex; --$i) {
            if (2 === $counter) {
                break;
                // we've seen both method we are interested in, so stop analyzing this class
            }
            if (!$this->isSetupOrTearDownMethod($tokens, $i)) {
                continue;
            }
            ++$counter;
            $visibility = $tokensAnalyzer->getMethodAttributes($i)['visibility'];
            if (\T_PUBLIC === $visibility) {
                $index = $tokens->getPrevTokenOfKind($i, [[\T_PUBLIC]]);
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PROTECTED, 'protected']);
                continue;
            }
            if (null === $visibility) {
                $tokens->insertAt($i, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PROTECTED, 'protected']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
            }
        }
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function isSetupOrTearDownMethod(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $isMethod = $tokens[$index]->isGivenKind(\T_FUNCTION) && !$tokensAnalyzer->isLambda($index);
        if (!$isMethod) {
            return \false;
        }
        $functionNameIndex = $tokens->getNextMeaningfulToken($index);
        $functionName = \strtolower($tokens[$functionNameIndex]->getContent());
        return 'setup' === $functionName || 'teardown' === $functionName;
    }
}
