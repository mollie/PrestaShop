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
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\OptionsResolver\Options;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class FinalInternalClassFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $intersect = \array_intersect_assoc($this->configuration['annotation_include'], $this->configuration['annotation_exclude']);
        if (\count($intersect)) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException($this->getName(), \sprintf('Annotation cannot be used in both the include and exclude list, got duplicates: "%s".', \implode('", "', \array_keys($intersect))));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Internal classes should be `final`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @internal\n */\nclass Sample\n{\n}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @CUSTOM\n */\nclass A{}\n\n/**\n * @CUSTOM\n * @not-fix\n */\nclass B{}\n", ['annotation_include' => ['@Custom'], 'annotation_exclude' => ['@not-fix']])], null, 'Changing classes to `final` might cause code execution to break.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before FinalStaticAccessFixer, ProtectedToPrivateFixer, SelfStaticAccessorFixer.
     * Must run after PhpUnitInternalClassFixer.
     */
    public function getPriority()
    {
        return 67;
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
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS) || !$this->isClassCandidate($tokens, $index)) {
                continue;
            }
            // make class final
            $tokens->insertAt($index, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_FINAL, 'final']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        $annotationsAsserts = [static function (array $values) {
            foreach ($values as $value) {
                if (!\is_string($value) || '' === $value) {
                    return \false;
                }
            }
            return \true;
        }];
        $annotationsNormalizer = static function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, array $value) {
            $newValue = [];
            foreach ($value as $key) {
                if ('@' === $key[0]) {
                    $key = \substr($key, 1);
                }
                $newValue[\strtolower($key)] = \true;
            }
            return $newValue;
        };
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOptionBuilder(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('annotation_include', 'Class level annotations tags that must be set in order to fix the class. (case insensitive)'), 'annotation-white-list'))->setAllowedTypes(['array'])->setAllowedValues($annotationsAsserts)->setDefault(['@internal'])->setNormalizer($annotationsNormalizer)->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOptionBuilder(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('annotation_exclude', 'Class level annotations tags that must be omitted to fix the class, even if all of the white list ones are used as well. (case insensitive)'), 'annotation-black-list'))->setAllowedTypes(['array'])->setAllowedValues($annotationsAsserts)->setDefault(['@final', '@Entity', 'MolliePrefix\\@ORM\\Entity', 'MolliePrefix\\@ORM\\Mapping\\Entity', 'MolliePrefix\\@Mapping\\Entity'])->setNormalizer($annotationsNormalizer)->getOption(), (new \MolliePrefix\PhpCsFixer\FixerConfiguration\AliasedFixerOptionBuilder(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('consider_absent_docblock_as_internal_class', 'Should classes without any DocBlock be fixed to final?'), 'consider-absent-docblock-as-internal-class'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * @param int $index T_CLASS index
     *
     * @return bool
     */
    private function isClassCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if ($tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind([\T_ABSTRACT, \T_FINAL, \T_NEW])) {
            return \false;
            // ignore class; it is abstract or already final
        }
        $docToken = $tokens[$tokens->getPrevNonWhitespace($index)];
        if (!$docToken->isGivenKind(\T_DOC_COMMENT)) {
            return $this->configuration['consider_absent_docblock_as_internal_class'];
        }
        $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($docToken->getContent());
        $tags = [];
        foreach ($doc->getAnnotations() as $annotation) {
            \MolliePrefix\PhpCsFixer\Preg::match('/@\\S+(?=\\s|$)/', $annotation->getContent(), $matches);
            $tag = \strtolower(\substr(\array_shift($matches), 1));
            foreach ($this->configuration['annotation_exclude'] as $tagStart => $true) {
                if (0 === \strpos($tag, $tagStart)) {
                    return \false;
                    // ignore class: class-level PHPDoc contains tag that has been excluded through configuration
                }
            }
            $tags[$tag] = \true;
        }
        foreach ($this->configuration['annotation_include'] as $tag => $true) {
            if (!isset($tags[$tag])) {
                return \false;
                // ignore class: class-level PHPDoc does not contain all tags that has been included through configuration
            }
        }
        return \true;
    }
}
