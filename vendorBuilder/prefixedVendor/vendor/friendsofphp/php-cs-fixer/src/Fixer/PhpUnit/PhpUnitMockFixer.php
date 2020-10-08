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
final class PhpUnitMockFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @var bool
     */
    private $fixCreatePartialMock;
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Usages of `->getMock` and `->getMockWithoutInvokingTheOriginalConstructor` methods MUST be replaced by `->createMock` or `->createPartialMock` methods.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $mock = $this->getMockWithoutInvokingTheOriginalConstructor("Foo");
        $mock1 = $this->getMock("Foo");
        $mock1 = $this->getMock("Bar", ["aaa"]);
        $mock1 = $this->getMock("Baz", ["aaa"], ["argument"]); // version with more than 2 params is not supported
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $mock1 = $this->getMock("Foo");
        $mock1 = $this->getMock("Bar", ["aaa"]); // version with multiple params is not supported
    }
}
', ['target' => \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_4])], null, 'Risky when PHPUnit classes are overridden or not accessible, or when project has PHPUnit incompatibilities.');
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
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->fixCreatePartialMock = \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::fulfills($this->configuration['target'], \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_5);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $phpUnitTestCaseIndicator = new \MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator();
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indexes) {
            for ($index = $indexes[0]; $index < $indexes[1]; ++$index) {
                if (!$tokens[$index]->isGivenKind(\T_OBJECT_OPERATOR)) {
                    continue;
                }
                $index = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$index]->equals([\T_STRING, 'getMockWithoutInvokingTheOriginalConstructor'], \false)) {
                    $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'createMock']);
                } elseif ($tokens[$index]->equals([\T_STRING, 'getMock'], \false)) {
                    $openingParenthesis = $tokens->getNextMeaningfulToken($index);
                    $closingParenthesis = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingParenthesis);
                    $argumentsCount = $argumentsAnalyzer->countArguments($tokens, $openingParenthesis, $closingParenthesis);
                    if (1 === $argumentsCount) {
                        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'createMock']);
                    } elseif (2 === $argumentsCount && \true === $this->fixCreatePartialMock) {
                        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'createPartialMock']);
                    }
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('target', 'Target version of PHPUnit.'))->setAllowedTypes(['string'])->setAllowedValues([\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_4, \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_5_5, \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST])->setDefault(\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST)->getOption()]);
    }
}
