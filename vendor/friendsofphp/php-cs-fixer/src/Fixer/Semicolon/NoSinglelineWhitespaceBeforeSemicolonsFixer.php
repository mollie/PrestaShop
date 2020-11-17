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
namespace MolliePrefix\PhpCsFixer\Fixer\Semicolon;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 */
final class NoSinglelineWhitespaceBeforeSemicolonsFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Single-line whitespace before closing semicolon are prohibited.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$this->foo() ;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after CombineConsecutiveIssetsFixer, FunctionToConstantFixer, NoEmptyStatementFixer, SingleImportPerStatementFixer.
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
        return $tokens->isTokenKindFound(';');
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equals(';') || !$tokens[$index - 1]->isWhitespace(" \t")) {
                continue;
            }
            if ($tokens[$index - 2]->equals(';')) {
                // do not remove all whitespace before the semicolon because it is also whitespace after another semicolon
                if (!$tokens[$index - 1]->equals(' ')) {
                    $tokens[$index - 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                }
            } elseif (!$tokens[$index - 2]->isComment()) {
                $tokens->clearAt($index - 1);
            }
        }
    }
}
