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
namespace MolliePrefix\PhpCsFixer\Fixer\Phpdoc;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use MolliePrefix\Symfony\Component\OptionsResolver\Options;
/**
 * Case sensitive tag replace fixer (does not process inline tags like {@inheritdoc}).
 *
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class PhpdocNoAliasTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('No alias PHPDoc tags should be used.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @property string $foo
 * @property-read string $bar
 *
 * @link baz
 */
final class Example
{
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @property string $foo
 * @property-read string $bar
 *
 * @link baz
 */
final class Example
{
}
', ['replacements' => ['link' => 'website']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAddMissingParamAnnotationFixer, PhpdocAlignFixer, PhpdocSingleLineVarSpacingFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 11;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $searchFor = \array_keys($this->configuration['replacements']);
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType($searchFor);
            if (empty($annotations)) {
                continue;
            }
            foreach ($annotations as $annotation) {
                $annotation->getTag()->setName($this->configuration['replacements'][$annotation->getTag()->getName()]);
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('replacements', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('replacements', 'Mapping between replaced annotations with new ones.'))->setAllowedTypes(['array'])->setNormalizer(static function (\MolliePrefix\Symfony\Component\OptionsResolver\Options $options, $value) {
            $normalizedValue = [];
            foreach ($value as $from => $to) {
                if (!\is_string($from)) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException('Tag to replace must be a string.');
                }
                if (!\is_string($to)) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Tag to replace to from "%s" must be a string.', $from));
                }
                if (1 !== \MolliePrefix\PhpCsFixer\Preg::match('#^\\S+$#', $to) || \false !== \strpos($to, '*/')) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Tag "%s" cannot be replaced by invalid tag "%s".', $from, $to));
                }
                $normalizedValue[\trim($from)] = \trim($to);
            }
            foreach ($normalizedValue as $from => $to) {
                if (isset($normalizedValue[$to])) {
                    throw new \MolliePrefix\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException(\sprintf('Cannot change tag "%1$s" to tag "%2$s", as the tag "%2$s" is configured to be replaced to "%3$s".', $from, $to, $normalizedValue[$to]));
                }
            }
            return $normalizedValue;
        })->setDefault(['property-read' => 'property', 'property-write' => 'property', 'type' => 'var', 'link' => 'see'])->getOption()], $this->getName());
    }
}
