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

use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\DocBlock\Line;
use MolliePrefix\PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitTestClassRequiresCoversFixer extends \MolliePrefix\PhpCsFixer\Fixer\AbstractPhpUnitFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Adds a default `@coversNothing` annotation to PHPUnit test classes that have no `@covers*` annotation.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertSame(a(), b());
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyPhpUnitClassFix(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        $classIndex = $tokens->getPrevTokenOfKind($startIndex, [[\T_CLASS]]);
        $prevIndex = $tokens->getPrevMeaningfulToken($classIndex);
        // don't add `@covers` annotation for abstract base classes
        if ($tokens[$prevIndex]->isGivenKind(\T_ABSTRACT)) {
            return;
        }
        $index = $tokens[$prevIndex]->isGivenKind(\T_FINAL) ? $prevIndex : $classIndex;
        $indent = $tokens[$index - 1]->isGivenKind(\T_WHITESPACE) ? \MolliePrefix\PhpCsFixer\Preg::replace('/^.*\\R*/', '', $tokens[$index - 1]->getContent()) : '';
        $prevIndex = $tokens->getPrevNonWhitespace($index);
        if ($tokens[$prevIndex]->isGivenKind(\T_DOC_COMMENT)) {
            $docIndex = $prevIndex;
            $docContent = $tokens[$docIndex]->getContent();
            // ignore one-line phpdocs like `/** foo */`, as there is no place to put new annotations
            if (\false === \strpos($docContent, "\n")) {
                return;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($docContent);
            // skip if already has annotation
            if (!empty($doc->getAnnotationsOfType(['covers', 'coversDefaultClass', 'coversNothing']))) {
                return;
            }
        } else {
            $docIndex = $index;
            $tokens->insertAt($docIndex, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \sprintf('/**%s%s */', $this->whitespacesConfig->getLineEnding(), $indent)]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, \sprintf('%s%s', $this->whitespacesConfig->getLineEnding(), $indent)])]);
            if (!$tokens[$docIndex - 1]->isGivenKind(\T_WHITESPACE)) {
                $extraNewLines = $this->whitespacesConfig->getLineEnding();
                if (!$tokens[$docIndex - 1]->isGivenKind(\T_OPEN_TAG)) {
                    $extraNewLines .= $this->whitespacesConfig->getLineEnding();
                }
                $tokens->insertAt($docIndex, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $extraNewLines . $indent])]);
                ++$docIndex;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$docIndex]->getContent());
        }
        $lines = $doc->getLines();
        \array_splice($lines, \count($lines) - 1, 0, [new \MolliePrefix\PhpCsFixer\DocBlock\Line(\sprintf('%s * @coversNothing%s', $indent, $this->whitespacesConfig->getLineEnding()))]);
        $tokens[$docIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \implode('', $lines)]);
    }
}
