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
 * Transform const/function import tokens.
 *
 * Performed transformations:
 * - T_CONST into CT::T_CONST_IMPORT
 * - T_FUNCTION into CT::T_FUNCTION_IMPORT
 *
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @internal
 */
final class ImportTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONST_IMPORT, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_FUNCTION_IMPORT];
    }
    /**
     * {@inheritdoc}
     */
    public function getRequiredPhpVersionId()
    {
        return 50600;
    }
    /**
     * {@inheritdoc}
     */
    public function process(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, \MolliePrefix\PhpCsFixer\Tokenizer\Token $token, $index)
    {
        if (!$token->isGivenKind([\T_CONST, \T_FUNCTION])) {
            return;
        }
        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];
        if ($prevToken->isGivenKind(\T_USE)) {
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->isGivenKind(\T_FUNCTION) ? \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_FUNCTION_IMPORT : \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONST_IMPORT, $token->getContent()]);
        }
    }
}
