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
 * Transforms for Constructor Property Promotion.
 *
 * Transform T_PUBLIC, T_PROTECTED and T_PRIVATE of Constructor Property Promotion into custom tokens.
 *
 * @internal
 */
final class ConstructorPromotionTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getRequiredPhpVersionId()
    {
        return 80000;
    }
    /**
     * {@inheritdoc}
     */
    public function process(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, \MolliePrefix\PhpCsFixer\Tokenizer\Token $token, $index)
    {
        if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
            return;
        }
        $index = $tokens->getNextMeaningfulToken($index);
        if (!$tokens[$index]->isGivenKind(\T_STRING) || '__construct' !== \strtolower($tokens[$index]->getContent())) {
            return;
        }
        /** @var int $openIndex */
        $openIndex = $tokens->getNextMeaningfulToken($index);
        // we are @ '(' now
        $closeIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
        for ($index = $openIndex; $index < $closeIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_PUBLIC)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, $tokens[$index]->getContent()]);
            } elseif ($tokens[$index]->isGivenKind(\T_PROTECTED)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, $tokens[$index]->getContent()]);
            } elseif ($tokens[$index]->isGivenKind(\T_PRIVATE)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, $tokens[$index]->getContent()]);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function getDeprecatedCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE];
    }
}
