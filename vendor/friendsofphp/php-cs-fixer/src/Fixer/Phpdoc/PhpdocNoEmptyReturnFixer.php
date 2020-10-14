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
use MolliePrefix\PhpCsFixer\DocBlock\Annotation;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 */
final class PhpdocNoEmptyReturnFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
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
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('`@return void` and `@return null` annotations should be omitted from PHPDoc.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @return null
*/
function foo() {}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @return void
*/
function foo() {}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoEmptyPhpdocFixer, PhpdocAlignFixer, PhpdocOrderFixer, PhpdocSeparationFixer, PhpdocTrimFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer, VoidReturnFixer.
     */
    public function getPriority()
    {
        return 10;
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
            $annotations = $doc->getAnnotationsOfType('return');
            if (empty($annotations)) {
                continue;
            }
            foreach ($annotations as $annotation) {
                $this->fixAnnotation($doc, $annotation);
            }
            $newContent = $doc->getContent();
            if ($newContent === $token->getContent()) {
                continue;
            }
            if ('' === $newContent) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }
    /**
     * Remove return void or return null annotations..
     */
    private function fixAnnotation(\MolliePrefix\PhpCsFixer\DocBlock\DocBlock $doc, \MolliePrefix\PhpCsFixer\DocBlock\Annotation $annotation)
    {
        $types = $annotation->getNormalizedTypes();
        if (1 === \count($types) && ('null' === $types[0] || 'void' === $types[0])) {
            $annotation->remove();
        }
    }
}
