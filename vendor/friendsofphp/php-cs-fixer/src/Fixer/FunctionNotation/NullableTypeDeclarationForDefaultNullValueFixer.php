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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\ArgumentAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author HypeMC
 */
final class NullableTypeDeclarationForDefaultNullValueFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Adds or removes `?` before type declarations for parameters with a default `null` value.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\nfunction sample(string \$str = null)\n{}\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100)), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\nfunction sample(?string \$str = null)\n{}\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100), ['use_nullable_type_declaration' => \false])], 'Rule is applied only in a PHP 7.1+ environment.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (\PHP_VERSION_ID < 70100) {
            return \false;
        }
        if (!$tokens->isTokenKindFound(\T_VARIABLE)) {
            return \false;
        }
        if (\PHP_VERSION_ID >= 70400 && $tokens->isTokenKindFound(\T_FN)) {
            return \true;
        }
        return $tokens->isTokenKindFound(\T_FUNCTION);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoUnreachableDefaultArgumentValueFixer.
     */
    public function getPriority()
    {
        return 1;
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('use_nullable_type_declaration', 'Whether to add or remove `?` before type declarations for parameters with a default `null` value.'))->setAllowedTypes(['bool'])->setDefault(\true)->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $functionsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        $tokenKinds = [\T_FUNCTION];
        if (\PHP_VERSION_ID >= 70400) {
            $tokenKinds[] = \T_FN;
        }
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind($tokenKinds)) {
                continue;
            }
            $arguments = $functionsAnalyzer->getFunctionArguments($tokens, $index);
            $this->fixFunctionParameters($tokens, $arguments);
        }
    }
    /**
     * @param ArgumentAnalysis[] $arguments
     */
    private function fixFunctionParameters(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $arguments)
    {
        foreach (\array_reverse($arguments) as $argumentInfo) {
            // If the parameter doesn't have a type declaration or a default value null we can continue
            if (!$argumentInfo->hasTypeAnalysis() || !$argumentInfo->hasDefault() || 'null' !== \strtolower($argumentInfo->getDefault())) {
                continue;
            }
            $argumentTypeInfo = $argumentInfo->getTypeAnalysis();
            if (\true === $this->configuration['use_nullable_type_declaration']) {
                if (!$argumentTypeInfo->isNullable()) {
                    $tokens->insertAt($argumentTypeInfo->getStartIndex(), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, '?']));
                }
            } else {
                if ($argumentTypeInfo->isNullable()) {
                    $tokens->removeTrailingWhitespace($argumentTypeInfo->getStartIndex());
                    $tokens->clearTokenAndMergeSurroundingWhitespace($argumentTypeInfo->getStartIndex());
                }
            }
        }
    }
}
