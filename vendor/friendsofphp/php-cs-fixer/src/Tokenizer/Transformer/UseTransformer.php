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
namespace MolliePrefix\PhpCsFixer\Tokenizer\Transformer;

use MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Transform T_USE into:
 * - CT::T_USE_TRAIT for imports,
 * - CT::T_USE_LAMBDA for lambda variable uses.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class UseTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA];
    }
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // Should run after CurlyBraceTransformer and before TypeColonTransformer
        return -5;
    }
    /**
     * {@inheritdoc}
     */
    public function getRequiredPhpVersionId()
    {
        return 50300;
    }
    /**
     * {@inheritdoc}
     */
    public function process(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, \MolliePrefix\PhpCsFixer\Tokenizer\Token $token, $index)
    {
        if ($token->isGivenKind(\T_USE) && $this->isUseForLambda($tokens, $index)) {
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA, $token->getContent()]);
            return;
        }
        // Only search inside class/trait body for `T_USE` for traits.
        // Cannot import traits inside interfaces or anywhere else
        if (!$token->isGivenKind([\T_CLASS, \T_TRAIT])) {
            return;
        }
        if ($tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(\T_DOUBLE_COLON)) {
            return;
        }
        $index = $tokens->getNextTokenOfKind($index, ['{']);
        $innerLimit = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
        while ($index < $innerLimit) {
            $token = $tokens[++$index];
            if (!$token->isGivenKind(\T_USE)) {
                continue;
            }
            if ($this->isUseForLambda($tokens, $index)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA, $token->getContent()]);
            } else {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT, $token->getContent()]);
            }
        }
    }
    /**
     * Check if token under given index is `use` statement for lambda function.
     *
     * @param int $index
     *
     * @return bool
     */
    private function isUseForLambda(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $nextToken = $tokens[$tokens->getNextMeaningfulToken($index)];
        // test `function () use ($foo) {}` case
        return $nextToken->equals('(');
    }
}
