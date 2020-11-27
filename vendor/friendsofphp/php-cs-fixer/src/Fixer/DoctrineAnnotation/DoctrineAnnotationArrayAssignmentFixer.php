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
namespace MolliePrefix\PhpCsFixer\Fixer\DoctrineAnnotation;

use MolliePrefix\Doctrine\Common\Annotations\DocLexer;
use MolliePrefix\PhpCsFixer\AbstractDoctrineAnnotationFixer;
use MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * Forces the configured operator for assignment in arrays in Doctrine Annotations.
 */
final class DoctrineAnnotationArrayAssignmentFixer extends \MolliePrefix\PhpCsFixer\AbstractDoctrineAnnotationFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Doctrine annotations must use configured operator for assignment in arrays.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @Foo({bar : \"baz\"})\n */\nclass Bar {}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @Foo({bar = \"baz\"})\n */\nclass Bar {}\n", ['operator' => ':'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before DoctrineAnnotationSpacesFixer.
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
        $options = parent::createConfigurationDefinition()->getOptions();
        $operator = new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('operator', 'The operator to use.');
        $options[] = $operator->setAllowedValues(['=', ':'])->setDefault('=')->getOption();
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver($options);
    }
    /**
     * {@inheritdoc}
     */
    protected function fixAnnotations(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens)
    {
        $scopes = [];
        foreach ($tokens as $token) {
            if ($token->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS)) {
                $scopes[] = 'annotation';
                continue;
            }
            if ($token->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_CURLY_BRACES)) {
                $scopes[] = 'array';
                continue;
            }
            if ($token->isType([\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS, \MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES])) {
                \array_pop($scopes);
                continue;
            }
            if ('array' === \end($scopes) && $token->isType([\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_EQUALS, \MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_COLON])) {
                $token->setContent($this->configuration['operator']);
            }
        }
    }
}
