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
namespace MolliePrefix\PhpCsFixer\Fixer\Operator;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 */
final class LogicalOperatorsFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Use `&&` and `||` logical operators instead of `and` and `or`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

if ($a == "foo" and ($b == "bar" or $c == "baz")) {
}
')], null, 'Risky, because you must double-check if using and/or with lower precedence was intentional.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_LOGICAL_AND, \T_LOGICAL_OR]);
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(\T_LOGICAL_AND)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_BOOLEAN_AND, '&&']);
            } elseif ($token->isGivenKind(\T_LOGICAL_OR)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_BOOLEAN_OR, '||']);
            }
        }
    }
}
