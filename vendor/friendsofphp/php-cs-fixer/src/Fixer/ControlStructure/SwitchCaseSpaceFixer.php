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
namespace MolliePrefix\PhpCsFixer\Fixer\ControlStructure;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for rules defined in PSR2 ¶5.2.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class SwitchCaseSpaceFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Removes extra spaces between colon and case value.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
    switch($a) {
        case 1   :
            break;
        default     :
            return 2;
    }
')]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_CASE, \T_DEFAULT]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind([\T_CASE, \T_DEFAULT])) {
                continue;
            }
            $ternariesCount = 0;
            for ($colonIndex = $index + 1;; ++$colonIndex) {
                // We have to skip ternary case for colons.
                if ($tokens[$colonIndex]->equals('?')) {
                    ++$ternariesCount;
                }
                if ($tokens[$colonIndex]->equalsAny([':', ';'])) {
                    if (0 === $ternariesCount) {
                        break;
                    }
                    --$ternariesCount;
                }
            }
            $valueIndex = $tokens->getPrevNonWhitespace($colonIndex);
            // skip if there is no space between the colon and previous token or is space after comment
            if ($valueIndex === $colonIndex - 1 || $tokens[$valueIndex]->isComment()) {
                continue;
            }
            $tokens->clearAt($valueIndex + 1);
        }
    }
}
