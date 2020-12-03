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
use MolliePrefix\PhpCsFixer\DocBlock\Line;
use MolliePrefix\PhpCsFixer\DocBlock\ShortDescription;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Nobu Funaki <nobu.funaki@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpdocTrimConsecutiveBlankLineSeparationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Removes extra blank lines after summary and after description in PHPDoc.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * Summary.
 *
 *
 * Description that contain 4 lines,
 *
 *
 * while 2 of them are blank!
 *
 *
 * @param string $foo
 *
 *
 * @dataProvider provideFixCases
 */
function fnc($foo) {}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return -41;
    }
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
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($token->getContent());
            $summaryEnd = (new \MolliePrefix\PhpCsFixer\DocBlock\ShortDescription($doc))->getEnd();
            if (null !== $summaryEnd) {
                $this->fixSummary($doc, $summaryEnd);
                $this->fixDescription($doc, $summaryEnd);
            }
            $this->fixAllTheRest($doc);
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }
    /**
     * @param int $summaryEnd
     */
    private function fixSummary(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, $summaryEnd)
    {
        $nonBlankLineAfterSummary = $this->findNonBlankLine($doc, $summaryEnd);
        $this->removeExtraBlankLinesBetween($doc, $summaryEnd, $nonBlankLineAfterSummary);
    }
    /**
     * @param int $summaryEnd
     */
    private function fixDescription(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, $summaryEnd)
    {
        $annotationStart = $this->findFirstAnnotationOrEnd($doc);
        // assuming the end of the Description appears before the first Annotation
        $descriptionEnd = $this->reverseFindLastUsefulContent($doc, $annotationStart);
        if (null === $descriptionEnd || $summaryEnd === $descriptionEnd) {
            return;
            // no Description
        }
        if ($annotationStart === \count($doc->getLines()) - 1) {
            return;
            // no content after Description
        }
        $this->removeExtraBlankLinesBetween($doc, $descriptionEnd, $annotationStart);
    }
    private function fixAllTheRest(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc)
    {
        $annotationStart = $this->findFirstAnnotationOrEnd($doc);
        $lastLine = $this->reverseFindLastUsefulContent($doc, \count($doc->getLines()) - 1);
        if (null !== $lastLine && $annotationStart !== $lastLine) {
            $this->removeExtraBlankLinesBetween($doc, $annotationStart, $lastLine);
        }
    }
    /**
     * @param int $from
     * @param int $to
     */
    private function removeExtraBlankLinesBetween(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, $from, $to)
    {
        for ($index = $from + 1; $index < $to; ++$index) {
            $line = $doc->getLine($index);
            $next = $doc->getLine($index + 1);
            $this->removeExtraBlankLine($line, $next);
        }
    }
    private function removeExtraBlankLine(\MolliePrefix\PhpCsFixer\DocBlock\Line $current, \MolliePrefix\PhpCsFixer\DocBlock\Line $next)
    {
        if (!$current->isTheEnd() && !$current->containsUsefulContent() && !$next->isTheEnd() && !$next->containsUsefulContent()) {
            $current->remove();
        }
    }
    /**
     * @param int $after
     *
     * @return null|int
     */
    private function findNonBlankLine(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, $after)
    {
        foreach ($doc->getLines() as $index => $line) {
            if ($index <= $after) {
                continue;
            }
            if ($line->containsATag() || $line->containsUsefulContent() || $line->isTheEnd()) {
                return $index;
            }
        }
        return null;
    }
    /**
     * @return int
     */
    private function findFirstAnnotationOrEnd(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc)
    {
        $index = null;
        foreach ($doc->getLines() as $index => $line) {
            if ($line->containsATag()) {
                return $index;
            }
        }
        return $index;
        // no Annotation, return the last line
    }
    /**
     * @param int $from
     *
     * @return null|int
     */
    private function reverseFindLastUsefulContent(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, $from)
    {
        for ($index = $from - 1; $index >= 0; --$index) {
            if ($doc->getLine($index)->containsUsefulContent()) {
                return $index;
            }
        }
        return null;
    }
}
