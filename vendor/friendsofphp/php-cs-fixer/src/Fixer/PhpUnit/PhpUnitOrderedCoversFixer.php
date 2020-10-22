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

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class PhpUnitOrderedCoversFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Order `@covers` annotation of PHPUnit tests.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @covers Foo
 * @covers Bar
 */
final class MyTest extends \\PHPUnit_Framework_TestCase
{}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after PhpUnitFqcnAnnotationFixer.
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
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_DOC_COMMENT) || 0 === \MolliePrefix\PhpCsFixer\Preg::match('/@covers\\s.+@covers\\s/s', $tokens[$index]->getContent())) {
                continue;
            }
            $docBlock = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$index]->getContent());
            $covers = $docBlock->getAnnotationsOfType('covers');
            $coversMap = [];
            foreach ($covers as $annotation) {
                $rawContent = $annotation->getContent();
                $comparableContent = \MolliePrefix\PhpCsFixer\Preg::replace('/\\*\\s*@covers\\s+(.+)/', '\\1', \strtolower(\trim($rawContent)));
                $coversMap[$comparableContent] = $rawContent;
            }
            $orderedCoversMap = $coversMap;
            \ksort($orderedCoversMap, \SORT_STRING);
            if ($orderedCoversMap === $coversMap) {
                continue;
            }
            $lines = $docBlock->getLines();
            foreach (\array_reverse($covers) as $annotation) {
                \array_splice($lines, $annotation->getStart(), $annotation->getEnd() - $annotation->getStart() + 1, \array_pop($orderedCoversMap));
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \implode('', $lines)]);
        }
    }
}
