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
 * Transform `|` operator into CT::T_TYPE_ALTERNATION in `} catch (ExceptionType1 | ExceptionType2 $e) {`.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class TypeAlternationTransformer extends \MolliePrefix\PhpCsFixer\Tokenizer\AbstractTransformer
{
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
        if (!$token->equals('|')) {
            return;
        }
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        $prevToken = $tokens[$prevIndex];
        if (!$prevToken->isGivenKind(\T_STRING)) {
            return;
        }
        do {
            $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if (null === $prevIndex) {
                break;
            }
            $prevToken = $tokens[$prevIndex];
            if ($prevToken->isGivenKind([\T_NS_SEPARATOR, \T_STRING])) {
                continue;
            }
            if ($prevToken->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_TYPE_ALTERNATION) || $prevToken->equals('(') && $tokens[$tokens->getPrevMeaningfulToken($prevIndex)]->isGivenKind(\T_CATCH)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_TYPE_ALTERNATION, '|']);
            }
            break;
        } while (\true);
    }
    /**
     * {@inheritdoc}
     */
    protected function getDeprecatedCustomTokens()
    {
        return [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_TYPE_ALTERNATION];
    }
}
