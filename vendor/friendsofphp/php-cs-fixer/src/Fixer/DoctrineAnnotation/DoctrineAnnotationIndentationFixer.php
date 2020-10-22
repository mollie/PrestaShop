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
use MolliePrefix\PhpCsFixer\Preg;
final class DoctrineAnnotationIndentationFixer extends \MolliePrefix\PhpCsFixer\AbstractDoctrineAnnotationFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Doctrine annotations must be indented with four spaces.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n *  @Foo(\n *   foo=\"foo\"\n *  )\n */\nclass Bar {}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n/**\n *  @Foo({@Bar,\n *   @Baz})\n */\nclass Bar {}\n", ['indent_mixed_lines' => \true])]);
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver(\array_merge(parent::createConfigurationDefinition()->getOptions(), [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('indent_mixed_lines', 'Whether to indent lines that have content before closing parenthesis.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]));
    }
    /**
     * {@inheritdoc}
     */
    protected function fixAnnotations(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens)
    {
        $annotationPositions = [];
        for ($index = 0, $max = \count($tokens); $index < $max; ++$index) {
            if (!$tokens[$index]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_AT)) {
                continue;
            }
            $annotationEndIndex = $tokens->getAnnotationEnd($index);
            if (null === $annotationEndIndex) {
                return;
            }
            $annotationPositions[] = [$index, $annotationEndIndex];
            $index = $annotationEndIndex;
        }
        $indentLevel = 0;
        foreach ($tokens as $index => $token) {
            if (!$token->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_NONE) || \false === \strpos($token->getContent(), "\n")) {
                continue;
            }
            if (!$this->indentationCanBeFixed($tokens, $index, $annotationPositions)) {
                continue;
            }
            $braces = $this->getLineBracesCount($tokens, $index);
            $delta = $braces[0] - $braces[1];
            $mixedBraces = 0 === $delta && $braces[0] > 0;
            $extraIndentLevel = 0;
            if ($indentLevel > 0 && ($delta < 0 || $mixedBraces)) {
                --$indentLevel;
                if ($this->configuration['indent_mixed_lines'] && $this->isClosingLineWithMeaningfulContent($tokens, $index)) {
                    $extraIndentLevel = 1;
                }
            }
            $token->setContent(\MolliePrefix\PhpCsFixer\Preg::replace('/(\\n( +\\*)?) *$/', '$1' . \str_repeat(' ', 4 * ($indentLevel + $extraIndentLevel) + 1), $token->getContent()));
            if ($delta > 0 || $mixedBraces) {
                ++$indentLevel;
            }
        }
    }
    /**
     * @param int $index
     *
     * @return int[]
     */
    private function getLineBracesCount(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens, $index)
    {
        $opening = 0;
        $closing = 0;
        while (isset($tokens[++$index])) {
            $token = $tokens[$index];
            if ($token->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_NONE) && \false !== \strpos($token->getContent(), "\n")) {
                break;
            }
            if ($token->isType([\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS, \MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_OPEN_CURLY_BRACES])) {
                ++$opening;
                continue;
            }
            if (!$token->isType([\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS, \MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES])) {
                continue;
            }
            if ($opening > 0) {
                --$opening;
            } else {
                ++$closing;
            }
        }
        return [$opening, $closing];
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function isClosingLineWithMeaningfulContent(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens, $index)
    {
        while (isset($tokens[++$index])) {
            $token = $tokens[$index];
            if ($token->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_NONE)) {
                if (\false !== \strpos($token->getContent(), "\n")) {
                    return \false;
                }
                continue;
            }
            return !$token->isType([\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS, \MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES]);
        }
        return \false;
    }
    /**
     * @param int               $newLineTokenIndex
     * @param array<array<int>> $annotationPositions Pairs of begin and end indexes of main annotations
     *
     * @return bool
     */
    private function indentationCanBeFixed(\MolliePrefix\PhpCsFixer\Doctrine\Annotation\Tokens $tokens, $newLineTokenIndex, array $annotationPositions)
    {
        foreach ($annotationPositions as $position) {
            if ($newLineTokenIndex >= $position[0] && $newLineTokenIndex <= $position[1]) {
                return \true;
            }
        }
        for ($index = $newLineTokenIndex + 1, $max = \count($tokens); $index < $max; ++$index) {
            $token = $tokens[$index];
            if (\false !== \strpos($token->getContent(), "\n")) {
                return \false;
            }
            return $tokens[$index]->isType(\MolliePrefix\Doctrine\Common\Annotations\DocLexer::T_AT);
        }
        return \false;
    }
}
