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
namespace MolliePrefix\PhpCsFixer\Tokenizer\Analyzer;

use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @internal
 */
final class BlocksAnalyzer
{
    /**
     * @param null|int $openIndex
     * @param null|int $closeIndex
     *
     * @return bool
     */
    public function isBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $openIndex, $closeIndex)
    {
        if (null === $openIndex || null === $closeIndex) {
            return \false;
        }
        if (!$tokens->offsetExists($openIndex)) {
            return \false;
        }
        if (!$tokens->offsetExists($closeIndex)) {
            return \false;
        }
        $blockType = $this->getBlockType($tokens[$openIndex]);
        if (null === $blockType) {
            return \false;
        }
        return $closeIndex === $tokens->findBlockEnd($blockType, $openIndex);
    }
    /**
     * @return null|int
     */
    private function getBlockType(\MolliePrefix\PhpCsFixer\Tokenizer\Token $token)
    {
        foreach (\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::getBlockEdgeDefinitions() as $blockType => $definition) {
            if ($token->equals($definition['start'])) {
                return $blockType;
            }
        }
        return null;
    }
}
