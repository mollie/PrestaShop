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
use MolliePrefix\PhpCsFixer\DocBlock\Annotation;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\DocBlock\Line;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
/**
 * @author Jefersson Nathan <malukenho.dev@gmail.com>
 */
final class PhpUnitSizeClassFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('All PHPUnit test cases should have `@small`, `@medium` or `@large` annotation to enable run time limits.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nclass MyTest extends TestCase {}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nclass MyTest extends TestCase {}\n", ['group' => 'medium'])], 'The special groups [small, medium, large] provides a way to identify tests that are taking long to be executed.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_STRING]);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $phpUnitTestCaseIndicator = new \MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator();
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens, \true) as $indexes) {
            $this->markClassSize($tokens, $indexes[0]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('group', 'Define a specific group to be used in case no group is already in use'))->setAllowedValues(['small', 'medium', 'large'])->setDefault('small')->getOption()]);
    }
    /**
     * @param int $startIndex
     */
    private function markClassSize(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex)
    {
        $classIndex = $tokens->getPrevTokenOfKind($startIndex, [[\T_CLASS]]);
        if ($this->isAbstractClass($tokens, $classIndex)) {
            return;
        }
        $docBlockIndex = $this->getDocBlockIndex($tokens, $classIndex);
        if ($this->hasDocBlock($tokens, $classIndex)) {
            $this->updateDocBlockIfNeeded($tokens, $docBlockIndex);
            return;
        }
        $this->createDocBlock($tokens, $docBlockIndex);
    }
    /**
     * @param int $i
     *
     * @return bool
     */
    private function isAbstractClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $i)
    {
        $typeIndex = $tokens->getPrevMeaningfulToken($i);
        return $tokens[$typeIndex]->isGivenKind(\T_ABSTRACT);
    }
    private function createDocBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = $this->detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        $group = $this->configuration['group'];
        $toInsert = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, '/**' . $lineEnd . "{$originalIndent} * @" . $group . $lineEnd . "{$originalIndent} */"]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnd . $originalIndent])];
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);
    }
    private function updateDocBlockIfNeeded(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$docBlockIndex]->getContent());
        if (!empty($this->filterDocBlock($doc))) {
            return;
        }
        $doc = $this->makeDocBlockMultiLineIfNeeded($doc, $tokens, $docBlockIndex);
        $lines = $this->addSizeAnnotation($doc, $tokens, $docBlockIndex);
        $lines = \implode('', $lines);
        $tokens[$docBlockIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $lines]);
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function hasDocBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
        return $tokens[$docBlockIndex]->isGivenKind(\T_DOC_COMMENT);
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function getDocBlockIndex(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_COMMENT]));
        return $index;
    }
    /**
     * @param int $index
     *
     * @return string
     */
    private function detectIndent(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index - 1]->isWhitespace()) {
            return '';
            // cannot detect indent
        }
        $explodedContent = \explode($this->whitespacesConfig->getLineEnding(), $tokens[$index - 1]->getContent());
        return \end($explodedContent);
    }
    /**
     * @param int $docBlockIndex
     *
     * @return Line[]
     */
    private function addSizeAnnotation(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $docBlock, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $lines = $docBlock->getLines();
        $originalIndent = $this->detectIndent($tokens, $docBlockIndex);
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $group = $this->configuration['group'];
        \array_splice($lines, -1, 0, $originalIndent . ' *' . $lineEnd . $originalIndent . ' * @' . $group . $lineEnd);
        return $lines;
    }
    /**
     * @param int $docBlockIndex
     *
     * @return DocBlock
     */
    private function makeDocBlockMultiLineIfNeeded(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $lines = $doc->getLines();
        if (1 === \count($lines) && empty($this->filterDocBlock($doc))) {
            $lines = $this->splitUpDocBlock($lines, $tokens, $docBlockIndex);
            return new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock(\implode('', $lines));
        }
        return $doc;
    }
    /**
     * Take a one line doc block, and turn it into a multi line doc block.
     *
     * @param Line[] $lines
     * @param int    $docBlockIndex
     *
     * @return Line[]
     */
    private function splitUpDocBlock($lines, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $lineContent = $this->getSingleLineDocBlockEntry($lines);
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = $this->detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        return [new \MolliePrefix\PhpCsFixer\DocBlock\Line('/**' . $lineEnd), new \MolliePrefix\PhpCsFixer\DocBlock\Line($originalIndent . ' * ' . $lineContent . $lineEnd), new \MolliePrefix\PhpCsFixer\DocBlock\Line($originalIndent . ' */')];
    }
    /**
     * @param Line|Line[]|string $line
     *
     * @return string
     */
    private function getSingleLineDocBlockEntry($line)
    {
        $line = $line[0];
        $line = \str_replace('*/', '', $line);
        $line = \trim($line);
        $line = \str_split($line);
        $i = \count($line);
        do {
            --$i;
        } while ('*' !== $line[$i] && '*' !== $line[$i - 1] && '/' !== $line[$i - 2]);
        if (' ' === $line[$i]) {
            ++$i;
        }
        $line = \array_slice($line, $i);
        return \implode('', $line);
    }
    /**
     * @return Annotation[][]
     */
    private function filterDocBlock(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc)
    {
        return \array_filter([$doc->getAnnotationsOfType('small'), $doc->getAnnotationsOfType('large'), $doc->getAnnotationsOfType('medium')]);
    }
}
