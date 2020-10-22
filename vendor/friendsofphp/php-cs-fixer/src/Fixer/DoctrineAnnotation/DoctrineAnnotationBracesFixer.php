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
use MolliePrefix\PhpCsFixer\Doctrine\Annotation\Token;
use MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * Adds braces to Doctrine annotations when missing.
 */
final class DoctrineAnnotationBracesFixer extends \MolliePrefix\PhpCsFixer\AbstractDoctrineAnnotationFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Doctrine annotations without arguments must use the configured syntax.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @Foo()\n */\nclass Bar {}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n * @Foo\n */\nclass Bar {}\n", ['syntax' => 'with_braces'])]);
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver(\array_merge(parent::createConfigurationDefinition()->getOptions(), [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('syntax', 'Whether to add or remove braces.'))->setAllowedValues(['with_braces', 'without_braces'])->setDefault('without_braces')->getOption()]));
    }
    /**
     * {@inheritdoc}
     */
    protected function fixAnnotations(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens)
    {
        if ('without_braces' === $this->configuration['syntax']) {
            $this->removesBracesFromAnnotations($tokens);
        } else {
            $this->addBracesToAnnotations($tokens);
        }
    }
    private function addBracesToAnnotations(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$tokens[$index]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_AT)) {
                continue;
            }
            $braceIndex = $tokens->getNextMeaningfulToken($index + 1);
            if (null !== $braceIndex && $tokens[$braceIndex]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS)) {
                continue;
            }
            $tokens->insertAt($index + 2, new \MolliePrefix\PhpCsFixer\Doctrine\Annotation\Token(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS, '('));
            $tokens->insertAt($index + 3, new \MolliePrefix\PhpCsFixer\Doctrine\Annotation\Token(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS, ')'));
        }
    }
    private function removesBracesFromAnnotations(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens)
    {
        for ($index = 0, $max = \count($tokens); $index < $max; ++$index) {
            if (!$tokens[$index]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_AT)) {
                continue;
            }
            $openBraceIndex = $tokens->getNextMeaningfulToken($index + 1);
            if (null === $openBraceIndex) {
                continue;
            }
            if (!$tokens[$openBraceIndex]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS)) {
                continue;
            }
            $closeBraceIndex = $tokens->getNextMeaningfulToken($openBraceIndex);
            if (null === $closeBraceIndex) {
                continue;
            }
            if (!$tokens[$closeBraceIndex]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS)) {
                continue;
            }
            for ($currentIndex = $index + 2; $currentIndex <= $closeBraceIndex; ++$currentIndex) {
                $tokens[$currentIndex]->clear();
            }
        }
    }
}
