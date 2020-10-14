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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitExpectationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * @var array<string, string>
     */
    private $methodMap = [];
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->methodMap = ['setExpectedException' => 'expectExceptionMessage'];
        if (\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::fulfills($this->configuration['target'], \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_6)) {
            $this->methodMap['setExpectedExceptionRegExp'] = 'expectExceptionMessageRegExp';
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Usages of `->setExpectedException*` methods MUST be replaced by `->expectException*` methods.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $this->setExpectedException("RuntimeException", "Msg", 123);
        foo();
    }

    public function testBar()
    {
        $this->setExpectedExceptionRegExp("RuntimeException", "/Msg.*/", 123);
        bar();
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $this->setExpectedException("RuntimeException", null, 123);
        foo();
    }

    public function testBar()
    {
        $this->setExpectedExceptionRegExp("RuntimeException", "/Msg.*/", 123);
        bar();
    }
}
', ['target' => \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_6]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $this->setExpectedException("RuntimeException", "Msg", 123);
        foo();
    }

    public function testBar()
    {
        $this->setExpectedExceptionRegExp("RuntimeException", "/Msg.*/", 123);
        bar();
    }
}
', ['target' => \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_2])], null, 'Risky when PHPUnit classes are overridden or not accessible, or when project has PHPUnit incompatibilities.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run after PhpUnitNoExpectationAnnotationFixer.
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
        return $tokens->isTokenKindFound(\T_CLASS);
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
            $this->fixExpectation($tokens, $indexes[0], $indexes[1]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('target', 'Target version of PHPUnit.'))->setAllowedTypes(['string'])->setAllowedValues([\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_2, \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_6, \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST])->setDefault(\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST)->getOption()]);
    }
    private function fixExpectation(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        $oldMethodSequence = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_VARIABLE, '$this']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_OBJECT_OPERATOR, '->']), [\T_STRING]];
        for ($index = $startIndex; $startIndex < $endIndex; ++$index) {
            $match = $tokens->findSequence($oldMethodSequence, $index);
            if (null === $match) {
                return;
            }
            list($thisIndex, , $index) = \array_keys($match);
            if (!isset($this->methodMap[$tokens[$index]->getContent()])) {
                continue;
            }
            $openIndex = $tokens->getNextTokenOfKind($index, ['(']);
            $closeIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
            $commaIndex = $tokens->getPrevMeaningfulToken($closeIndex);
            if ($tokens[$commaIndex]->equals(',')) {
                $tokens->removeTrailingWhitespace($commaIndex);
                $tokens->clearAt($commaIndex);
            }
            $arguments = $argumentsAnalyzer->getArguments($tokens, $openIndex, $closeIndex);
            $argumentsCnt = \count($arguments);
            $argumentsReplacements = ['expectException', $this->methodMap[$tokens[$index]->getContent()], 'expectExceptionCode'];
            $indent = $this->whitespacesConfig->getLineEnding() . $this->detectIndent($tokens, $thisIndex);
            $isMultilineWhitespace = \false;
            for ($cnt = $argumentsCnt - 1; $cnt >= 1; --$cnt) {
                $argStart = \array_keys($arguments)[$cnt];
                $argBefore = $tokens->getPrevMeaningfulToken($argStart);
                if ('expectExceptionMessage' === $argumentsReplacements[$cnt]) {
                    $paramIndicatorIndex = $tokens->getNextMeaningfulToken($argBefore);
                    $afterParamIndicatorIndex = $tokens->getNextMeaningfulToken($paramIndicatorIndex);
                    if ($tokens[$paramIndicatorIndex]->equals([\T_STRING, 'null'], \false) && $tokens[$afterParamIndicatorIndex]->equals(')')) {
                        if ($tokens[$argBefore + 1]->isWhitespace()) {
                            $tokens->clearTokenAndMergeSurroundingWhitespace($argBefore + 1);
                        }
                        $tokens->clearTokenAndMergeSurroundingWhitespace($argBefore);
                        $tokens->clearTokenAndMergeSurroundingWhitespace($paramIndicatorIndex);
                        continue;
                    }
                }
                $isMultilineWhitespace = $isMultilineWhitespace || $tokens[$argStart]->isWhitespace() && !$tokens[$argStart]->isWhitespace(" \t");
                $tokensOverrideArgStart = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $indent]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_VARIABLE, '$this']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_OBJECT_OPERATOR, '->']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $argumentsReplacements[$cnt]]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token('(')];
                $tokensOverrideArgBefore = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token(')'), new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';')];
                if ($isMultilineWhitespace) {
                    \array_push($tokensOverrideArgStart, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $indent . $this->whitespacesConfig->getIndent()]));
                    \array_unshift($tokensOverrideArgBefore, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $indent]));
                }
                if ($tokens[$argStart]->isWhitespace()) {
                    $tokens->overrideRange($argStart, $argStart, $tokensOverrideArgStart);
                } else {
                    $tokens->insertAt($argStart, $tokensOverrideArgStart);
                }
                $tokens->overrideRange($argBefore, $argBefore, $tokensOverrideArgBefore);
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'expectException']);
        }
    }
    /**
     * @param int $index
     *
     * @return string
     */
    private function detectIndent(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index - 1]->isWhitespace()) {
            return '';
            // cannot detect indent
        }
        $explodedContent = \explode("\n", $tokens[$index - 1]->getContent());
        return \end($explodedContent);
    }
}
