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
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 * @author Andreas Möller <am@localheinz.com>
 */
final class PhpdocOrderByValueFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Order phpdoc tags by value.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @covers Foo
 * @covers Bar
 */
final class MyTest extends \\PHPUnit_Framework_TestCase
{}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @author Bob
 * @author Alice
 */
final class MyTest extends \\PHPUnit_Framework_TestCase
{}
', ['annotations' => ['author']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpUnitFqcnAnnotationFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_DOC_COMMENT]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if ([] === $this->configuration['annotations']) {
            return;
        }
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            foreach ($this->configuration['annotations'] as $type) {
                $findPattern = \sprintf('/@%s\\s.+@%s\\s/s', $type, $type);
                if (!$tokens[$index]->isGivenKind(\T_DOC_COMMENT) || 0 === \MolliePrefix\PhpCsFixer\Preg::match($findPattern, $tokens[$index]->getContent())) {
                    continue;
                }
                $docBlock = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$index]->getContent());
                $annotations = $docBlock->getAnnotationsOfType($type);
                $annotationMap = [];
                $replacePattern = \sprintf('/\\*\\s*@%s\\s+(.+)/', $type);
                foreach ($annotations as $annotation) {
                    $rawContent = $annotation->getContent();
                    $comparableContent = \MolliePrefix\PhpCsFixer\Preg::replace($replacePattern, '\\1', \strtolower(\trim($rawContent)));
                    $annotationMap[$comparableContent] = $rawContent;
                }
                $orderedAnnotationMap = $annotationMap;
                \ksort($orderedAnnotationMap, \SORT_STRING);
                if ($orderedAnnotationMap === $annotationMap) {
                    continue;
                }
                $lines = $docBlock->getLines();
                foreach (\array_reverse($annotations) as $annotation) {
                    \array_splice($lines, $annotation->getStart(), $annotation->getEnd() - $annotation->getStart() + 1, \array_pop($orderedAnnotationMap));
                }
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \implode('', $lines)]);
            }
        }
    }
    protected function createConfigurationDefinition()
    {
        $allowedValues = ['author', 'covers', 'coversNothing', 'dataProvider', 'depends', 'group', 'internal', 'requires', 'throws', 'uses'];
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('annotations', 'List of annotations to order, e.g. `["covers"]`.'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset($allowedValues)])->setDefault(['covers'])->getOption()]);
    }
}
