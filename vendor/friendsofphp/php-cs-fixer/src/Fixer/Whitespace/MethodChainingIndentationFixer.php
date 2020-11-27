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
namespace MolliePrefix\PhpCsFixer\Fixer\Whitespace;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Vladimir Boliev <voff.web@gmail.com>
 */
final class MethodChainingIndentationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Method chaining MUST be properly indented. Method chaining with different levels of indentation is not supported.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$user->setEmail('voff.web@gmail.com')\n         ->setPassword('233434');\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before ArrayIndentationFixer, MethodArgumentSpaceFixer.
     * Must run after BracesFixer.
     */
    public function getPriority()
    {
        return -29;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_OBJECT_OPERATOR);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_OBJECT_OPERATOR)) {
                continue;
            }
            if ($this->canBeMovedToNextLine($index, $tokens)) {
                $newline = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding]);
                if ($tokens[$index - 1]->isWhitespace()) {
                    $tokens[$index - 1] = $newline;
                } else {
                    $tokens->insertAt($index, $newline);
                    ++$index;
                }
            }
            $currentIndent = $this->getIndentAt($tokens, $index - 1);
            if (null === $currentIndent) {
                continue;
            }
            $expectedIndent = $this->getExpectedIndentAt($tokens, $index);
            if ($currentIndent !== $expectedIndent) {
                $tokens[$index - 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . $expectedIndent]);
            }
        }
    }
    /**
     * @param int $index index of the first token on the line to indent
     *
     * @return string
     */
    private function getExpectedIndentAt(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $index = $tokens->getPrevMeaningfulToken($index);
        $indent = $this->whitespacesConfig->getIndent();
        for ($i = $index; $i >= 0; --$i) {
            if ($tokens[$i]->equals(')')) {
                $i = $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $i);
            }
            $currentIndent = $this->getIndentAt($tokens, $i);
            if (null === $currentIndent) {
                continue;
            }
            if ($this->currentLineRequiresExtraIndentLevel($tokens, $i, $index)) {
                return $currentIndent . $indent;
            }
            return $currentIndent;
        }
        return $indent;
    }
    /**
     * @param int $index position of the T_OBJECT_OPERATOR token
     *
     * @return bool
     */
    private function canBeMovedToNextLine($index, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $prevMeaningful = $tokens->getPrevMeaningfulToken($index);
        $hasCommentBefore = \false;
        for ($i = $index - 1; $i > $prevMeaningful; --$i) {
            if ($tokens[$i]->isComment()) {
                $hasCommentBefore = \true;
                continue;
            }
            if ($tokens[$i]->isWhitespace() && 1 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $tokens[$i]->getContent())) {
                return $hasCommentBefore;
            }
        }
        return \false;
    }
    /**
     * @param int $index index of the indentation token
     *
     * @return null|string
     */
    private function getIndentAt(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (1 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R{1}(\\h*)$/', $this->getIndentContentAt($tokens, $index), $matches)) {
            return $matches[1];
        }
        return null;
    }
    private function getIndentContentAt(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$index]->isGivenKind([\T_WHITESPACE, \T_INLINE_HTML])) {
            return '';
        }
        $content = $tokens[$index]->getContent();
        if ($tokens[$index]->isWhitespace() && $tokens[$index - 1]->isGivenKind(\T_OPEN_TAG)) {
            $content = $tokens[$index - 1]->getContent() . $content;
        }
        if (\MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $content)) {
            return $content;
        }
        return '';
    }
    /**
     * @param int $start index of first meaningful token on previous line
     * @param int $end   index of last token on previous line
     *
     * @return bool
     */
    private function currentLineRequiresExtraIndentLevel(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end)
    {
        if ($tokens[$start + 1]->isGivenKind(\T_OBJECT_OPERATOR)) {
            return \false;
        }
        if ($tokens[$end]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_BRACE_CLASS_INSTANTIATION_CLOSE)) {
            return \true;
        }
        return !$tokens[$end]->equals(')') || $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $end) >= $start;
    }
}
