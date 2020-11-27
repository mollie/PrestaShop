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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * Fixer for rules defined in PSR2 ¶4.3, ¶4.5.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class VisibilityRequiredFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class Sample
{
    var $a;
    static protected $var_foo2;

    function A()
    {
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php
class Sample
{
    const SAMPLE = 1;
}
', new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100), ['elements' => ['const']])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(\MolliePrefix\PhpCsFixer\Tokenizer\Token::getClassyTokenKinds());
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('elements', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('elements', 'The structural elements to fix (PHP >= 7.1 required for `const`).'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset(['property', 'method', 'const'])])->setDefault(['property', 'method'])->getOption()], $this->getName());
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $elements = $tokensAnalyzer->getClassyElements();
        $propertyTypeDeclarationKinds = [\T_STRING, \T_NS_SEPARATOR, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_TYPEHINT];
        foreach (\array_reverse($elements, \true) as $index => $element) {
            if (!\in_array($element['type'], $this->configuration['elements'], \true)) {
                continue;
            }
            if (\PHP_VERSION_ID < 70100 && 'const' === $element['type']) {
                continue;
            }
            $abstractFinalIndex = null;
            $visibilityIndex = null;
            $staticIndex = null;
            $typeIndex = null;
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $expectedKinds = [\T_ABSTRACT, \T_FINAL, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_STATIC, \T_VAR];
            if ('property' === $element['type']) {
                $expectedKinds = \array_merge($expectedKinds, $propertyTypeDeclarationKinds);
            }
            while ($tokens[$prevIndex]->isGivenKind($expectedKinds)) {
                if ($tokens[$prevIndex]->isGivenKind([\T_ABSTRACT, \T_FINAL])) {
                    $abstractFinalIndex = $prevIndex;
                } elseif ($tokens[$prevIndex]->isGivenKind(\T_STATIC)) {
                    $staticIndex = $prevIndex;
                } elseif ($tokens[$prevIndex]->isGivenKind($propertyTypeDeclarationKinds)) {
                    $typeIndex = $prevIndex;
                } else {
                    $visibilityIndex = $prevIndex;
                }
                $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            }
            if (null !== $typeIndex) {
                $index = $typeIndex;
            }
            if ($tokens[$prevIndex]->equals(',')) {
                continue;
            }
            if (null !== $staticIndex) {
                if ($this->isKeywordPlacedProperly($tokens, $staticIndex, $index)) {
                    $index = $staticIndex;
                } else {
                    $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $staticIndex, $index);
                }
            }
            if (null === $visibilityIndex) {
                $tokens->insertAt($index, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PUBLIC, 'public']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
            } else {
                if ($tokens[$visibilityIndex]->isGivenKind(\T_VAR)) {
                    $tokens[$visibilityIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_PUBLIC, 'public']);
                }
                if ($this->isKeywordPlacedProperly($tokens, $visibilityIndex, $index)) {
                    $index = $visibilityIndex;
                } else {
                    $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $visibilityIndex, $index);
                }
            }
            if (null === $abstractFinalIndex) {
                continue;
            }
            if ($this->isKeywordPlacedProperly($tokens, $abstractFinalIndex, $index)) {
                continue;
            }
            $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $abstractFinalIndex, $index);
        }
    }
    /**
     * @param int $keywordIndex
     * @param int $comparedIndex
     *
     * @return bool
     */
    private function isKeywordPlacedProperly(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $keywordIndex, $comparedIndex)
    {
        return $keywordIndex + 2 === $comparedIndex && ' ' === $tokens[$keywordIndex + 1]->getContent();
    }
    /**
     * @param int $fromIndex
     * @param int $toIndex
     */
    private function moveTokenAndEnsureSingleSpaceFollows(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $fromIndex, $toIndex)
    {
        $tokens->insertAt($toIndex, [$tokens[$fromIndex], new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
        $tokens->clearAt($fromIndex);
        if ($tokens[$fromIndex + 1]->isWhitespace()) {
            $tokens->clearAt($fromIndex + 1);
        }
    }
}
