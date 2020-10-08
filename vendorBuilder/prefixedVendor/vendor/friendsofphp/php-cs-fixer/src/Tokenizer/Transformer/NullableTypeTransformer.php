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
 * Transform `?` operator into CT::T_NULLABLE_TYPE in `function foo(?Bar $b) {}`.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class NullableTypeTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE];
    }
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // needs to run after TypeColonTransformer
        return -20;
    }
    /**
     * {@inheritdoc}
     */
    public function getRequiredPhpVersionId()
    {
        return 70100;
    }
    /**
     * {@inheritdoc}
     */
    public function process(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, \MolliePrefix\PhpCsFixer\Tokenizer\Token $token, $index)
    {
        if (!$token->equals('?')) {
            return;
        }
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        $prevToken = $tokens[$prevIndex];
        if ($prevToken->equalsAny(['(', ',', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_TYPE_COLON], [\T_PRIVATE], [\T_PROTECTED], [\T_PUBLIC], [\T_VAR], [\T_STATIC]])) {
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, '?']);
        }
    }
}
