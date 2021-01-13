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
 * Transform named argument tokens.
 *
 * @author SpacePossum
 *
 * @internal
 */
final class NamedArgumentTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // needs to run after TypeColonTransformer
        return -15;
    }
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
        if (!$tokens[$index]->equals(':')) {
            return;
        }
        $stringIndex = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$stringIndex]->isGivenKind(\T_STRING)) {
            return;
        }
        $preStringIndex = $tokens->getPrevMeaningfulToken($stringIndex);
        // if equals any [';', '{', '}', [T_OPEN_TAG]] than it is a goto label
        // if equals ')' than likely it is a type colon, but sure not a name argument
        // if equals '?' than it is part of ternary statement
        if (!$tokens[$preStringIndex]->equalsAny([',', '('])) {
            return;
        }
        $tokens[$stringIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NAMED_ARGUMENT_NAME, $tokens[$stringIndex]->getContent()]);
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NAMED_ARGUMENT_COLON, ':']);
    }
    /**
     * {@inheritdoc}
     */
    protected function getDeprecatedCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NAMED_ARGUMENT_COLON, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NAMED_ARGUMENT_NAME];
    }
}
