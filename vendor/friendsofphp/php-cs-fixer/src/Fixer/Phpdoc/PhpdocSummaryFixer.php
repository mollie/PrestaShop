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
use MolliePrefix\PhpCsFixer\DocBlock\ShortDescription;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 */
final class PhpdocSummaryFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPDoc summary should end in either a full stop, exclamation mark, or question mark.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * Foo function is great
 */
function foo () {}
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
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($token->getContent());
            $end = (new \MolliePrefix\PhpCsFixer\DocBlock\ShortDescription($doc))->getEnd();
            if (null !== $end) {
                $line = $doc->getLine($end);
                $content = \rtrim($line->getContent());
                if (!$this->isCorrectlyFormatted($content)) {
                    $line->setContent($content . '.' . $this->whitespacesConfig->getLineEnding());
                    $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
                }
            }
        }
    }
    /**
     * Is the last line of the short description correctly formatted?
     *
     * @param string $content
     *
     * @return bool
     */
    private function isCorrectlyFormatted($content)
    {
        if (\false !== \stripos($content, '{@inheritdoc}')) {
            return \true;
        }
        return $content !== \rtrim($content, '.。!?¡¿！？');
    }
}
