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
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class NoWhitespaceInBlankLineFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Remove trailing whitespace at the end of blank lines.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n   \n\$a = 1;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after CombineConsecutiveIssetsFixer, CombineConsecutiveUnsetsFixer, FunctionToConstantFixer, NoEmptyCommentFixer, NoEmptyPhpdocFixer, NoEmptyStatementFixer, NoUselessElseFixer, NoUselessReturnFixer.
     */
    public function getPriority()
    {
        return -19;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        // skip first as it cannot be a white space token
        for ($i = 1, $count = \count($tokens); $i < $count; ++$i) {
            if ($tokens[$i]->isWhitespace()) {
                $this->fixWhitespaceToken($tokens, $i);
            }
        }
    }
    /**
     * @param int $index
     */
    private function fixWhitespaceToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $content = $tokens[$index]->getContent();
        $lines = \MolliePrefix\PhpCsFixer\Preg::split("/(\r\n|\n)/", $content);
        $lineCount = \count($lines);
        if ($lineCount > 2 || $lineCount > 0 && (!isset($tokens[$index + 1]) || $tokens[$index - 1]->isGivenKind(\T_OPEN_TAG))) {
            $lMax = isset($tokens[$index + 1]) ? $lineCount - 1 : $lineCount;
            $lStart = 1;
            if ($tokens[$index - 1]->isGivenKind(\T_OPEN_TAG) && "\n" === \substr($tokens[$index - 1]->getContent(), -1)) {
                $lStart = 0;
            }
            for ($l = $lStart; $l < $lMax; ++$l) {
                $lines[$l] = \MolliePrefix\PhpCsFixer\Preg::replace('/^\\h+$/', '', $lines[$l]);
            }
            $content = \implode($this->whitespacesConfig->getLineEnding(), $lines);
            if ('' !== $content) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $content]);
            } else {
                $tokens->clearAt($index);
            }
        }
    }
}
