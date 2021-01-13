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

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
/**
 * Case sensitive tag replace fixer (does not process inline tags like {@inheritdoc}).
 *
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class PhpdocNoAliasTagFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
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
        return parent::getPriority();
    }
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        /** @var GeneralPhpdocTagRenameFixer $generalPhpdocTagRenameFixer */
        $generalPhpdocTagRenameFixer = $this->proxyFixers['general_phpdoc_tag_rename'];
        try {
            $generalPhpdocTagRenameFixer->configure(['fix_annotation' => \true, 'fix_inline' => \false, 'replacements' => $this->configuration['replacements'], 'case_sensitive' => \true]);
        } catch (\MolliePrefix\PhpCsFixer\ConfigurationException\InvalidConfigurationException $exception) {
            throw new \MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException($this->getName(), \MolliePrefix\PhpCsFixer\Preg::replace('/^\\[.+?\\] /', '', $exception->getMessage()), $exception);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('replacements', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('replacements', 'Mapping between replaced annotations with new ones.'))->setAllowedTypes(['array'])->setDefault(['property-read' => 'property', 'property-write' => 'property', 'type' => 'var', 'link' => 'see'])->getOption()], $this->getName());
    }
    /**
     * {@inheritdoc}
     */
    protected function createProxyFixers()
    {
        return [new \MolliePrefix\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocTagRenameFixer()];
    }
}
