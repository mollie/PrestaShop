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
 * Fix inline tags and make inheritdoc tag always inline.
 */
final class PhpdocInlineTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Fix PHPDoc inline tags, make `@inheritdoc` always inline.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @{TUTORIAL}
 * {{ @link }}
 * {@examples}
 * @inheritdocs
 */
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
            $content = $token->getContent();
            // Move `@` inside tag, for example @{tag} -> {@tag}, replace multiple curly brackets,
            // remove spaces between '{' and '@', remove 's' at the end of tag.
            // Make sure the tags are written in lower case, remove white space between end
            // of text and closing bracket and between the tag and inline comment.
            $content = \MolliePrefix\PhpCsFixer\Preg::replaceCallback('#(?:@{+|{+\\h*@)[ \\t]*(example|id|internal|inheritdoc|link|source|toc|tutorial)s?([^}]*)(?:}+)#i', static function (array $matches) {
                $doc = \trim($matches[2]);
                if ('' === $doc) {
                    return '{@' . \strtolower($matches[1]) . '}';
                }
                return '{@' . \strtolower($matches[1]) . ' ' . $doc . '}';
            }, $content);
            // Always make inheritdoc inline using with '{' '}' when needed,
            // make sure lowercase.
            $content = \MolliePrefix\PhpCsFixer\Preg::replace('#(?<!{)@inheritdocs?(?!})#i', '{@inheritdoc}', $content);
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $content]);
        }
    }
}
