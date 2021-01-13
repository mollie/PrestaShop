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
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitStrictFixer extends \MolliePrefix\PhpCsFixer\Fixer\AbstractPhpUnitFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    private static $assertionMap = ['assertAttributeEquals' => 'assertAttributeSame', 'assertAttributeNotEquals' => 'assertAttributeNotSame', 'assertEquals' => 'assertSame', 'assertNotEquals' => 'assertNotSame'];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPUnit methods like `assertSame` should be used instead of `assertEquals`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertAttributeEquals(a(), b());
        $this->assertAttributeNotEquals(a(), b());
        $this->assertEquals(a(), b());
        $this->assertNotEquals(a(), b());
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertAttributeEquals(a(), b());
        $this->assertAttributeNotEquals(a(), b());
        $this->assertEquals(a(), b());
        $this->assertNotEquals(a(), b());
    }
}
', ['assertions' => ['assertEquals']])], null, 'Risky when any of the functions are overridden or when testing object equality.');
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
    protected function applyPhpUnitClassFix(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $argumentsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer();
        $functionsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        foreach ($this->configuration['assertions'] as $methodBefore) {
            $methodAfter = self::$assertionMap[$methodBefore];
            for ($index = $startIndex; $index < $endIndex; ++$index) {
                $methodIndex = $tokens->getNextTokenOfKind($index, [[\T_STRING, $methodBefore]]);
                if (null === $methodIndex) {
                    break;
                }
                if (!$functionsAnalyzer->isTheSameClassCall($tokens, $methodIndex)) {
                    continue;
                }
                $openingParenthesisIndex = $tokens->getNextMeaningfulToken($methodIndex);
                $argumentsCount = $argumentsAnalyzer->countArguments($tokens, $openingParenthesisIndex, $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingParenthesisIndex));
                if (2 === $argumentsCount || 3 === $argumentsCount) {
                    $tokens[$methodIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $methodAfter]);
                }
                $index = $methodIndex;
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('assertions', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('assertions', 'List of assertion methods to fix.'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset(\array_keys(self::$assertionMap))])->setDefault(['assertAttributeEquals', 'assertAttributeNotEquals', 'assertEquals', 'assertNotEquals'])->getOption()], $this->getName());
    }
}
