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
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class PhpdocVarAnnotationCorrectOrderFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('`@var` and `@type` annotations must have type and name in the correct order.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/** @var $foo int */
$foo = 2 + 2;
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            if (\false === \stripos($token->getContent(), '@var') && \false === \stripos($token->getContent(), '@type')) {
                continue;
            }
            $newContent = \MolliePrefix\PhpCsFixer\Preg::replace('/(@(?:type|var)\\s*)(\\$\\S+)(\\h+)([^\\$](?:[^<\\s]|<[^>]*>)*)(\\s|\\*)/i', '$1$4$3$2$5', $token->getContent());
            if ($newContent === $token->getContent()) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), $newContent]);
        }
    }
}
