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

use MolliePrefix\PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class PhpUnitDedicateAssertInternalTypeFixer extends \MolliePrefix\PhpCsFixer\Fixer\AbstractPhpUnitFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @var array
     */
    private $typeToDedicatedAssertMap = ['array' => 'assertIsArray', 'boolean' => 'assertIsBool', 'bool' => 'assertIsBool', 'double' => 'assertIsFloat', 'float' => 'assertIsFloat', 'integer' => 'assertIsInt', 'int' => 'assertIsInt', 'null' => 'assertNull', 'numeric' => 'assertIsNumeric', 'object' => 'assertIsObject', 'real' => 'assertIsFloat', 'resource' => 'assertIsResource', 'string' => 'assertIsString', 'scalar' => 'assertIsScalar', 'callable' => 'assertIsCallable', 'iterable' => 'assertIsIterable'];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPUnit assertions like `assertIsArray` should be used over `assertInternalType`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit\\Framework\\TestCase
{
    public function testMe()
    {
        $this->assertInternalType("array", $var);
        $this->assertInternalType("boolean", $var);
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit\\Framework\\TestCase
{
    public function testMe()
    {
        $this->assertInternalType("array", $var);
        $this->assertInternalType("boolean", $var);
    }
}
', ['target' => \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_7_5])], null, 'Risky when PHPUnit methods are overridden or when project has PHPUnit incompatibilities.');
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
     * Must run after PhpUnitDedicateAssertFixer.
     */
    public function getPriority()
    {
        return -16;
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('target', 'Target version of PHPUnit.'))->setAllowedTypes(['string'])->setAllowedValues([\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_7_5, \MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST])->setDefault(\MolliePrefix\PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion::VERSION_NEWEST)->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyPhpUnitClassFix(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $anonymousClassIndexes = [];
        $tokenAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $startIndex; $index < $endIndex; ++$index) {
            if (!$tokens[$index]->isClassy() || !$tokenAnalyzer->isAnonymousClass($index)) {
                continue;
            }
            $openingBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
            $closingBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $openingBraceIndex);
            $anonymousClassIndexes[$closingBraceIndex] = $openingBraceIndex;
        }
        for ($index = $endIndex - 1; $index > $startIndex; --$index) {
            if (isset($anonymousClassIndexes[$index])) {
                $index = $anonymousClassIndexes[$index];
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }
            $functionName = \strtolower($tokens[$index]->getContent());
            if ('assertinternaltype' !== $functionName && 'assertnotinternaltype' !== $functionName) {
                continue;
            }
            $bracketTokenIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$bracketTokenIndex]->equals('(')) {
                continue;
            }
            $expectedTypeTokenIndex = $tokens->getNextMeaningfulToken($bracketTokenIndex);
            $expectedTypeToken = $tokens[$expectedTypeTokenIndex];
            if (!$expectedTypeToken->equals([\T_CONSTANT_ENCAPSED_STRING])) {
                continue;
            }
            $expectedType = \trim($expectedTypeToken->getContent(), '\'"');
            if (!isset($this->typeToDedicatedAssertMap[$expectedType])) {
                continue;
            }
            $commaTokenIndex = $tokens->getNextMeaningfulToken($expectedTypeTokenIndex);
            if (!$tokens[$commaTokenIndex]->equals(',')) {
                continue;
            }
            $newAssertion = $this->typeToDedicatedAssertMap[$expectedType];
            if ('assertnotinternaltype' === $functionName) {
                $newAssertion = \str_replace('Is', 'IsNot', $newAssertion);
                $newAssertion = \str_replace('Null', 'NotNull', $newAssertion);
            }
            $nextMeaningfulTokenIndex = $tokens->getNextMeaningfulToken($commaTokenIndex);
            $tokens->overrideRange($index, $nextMeaningfulTokenIndex - 1, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $newAssertion]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token('(')]);
        }
    }
}
