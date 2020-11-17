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
namespace MolliePrefix\PhpCsFixer\Fixer\Strict;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class StrictComparisonFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Comparisons should be strict.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$a = 1== \$b;\n")], null, 'Changing comparisons to strict might change code behavior.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer.
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
        return $tokens->isAnyTokenKindsFound([\T_IS_EQUAL, \T_IS_NOT_EQUAL]);
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
        static $map = [\T_IS_EQUAL => ['id' => \T_IS_IDENTICAL, 'content' => '==='], \T_IS_NOT_EQUAL => ['id' => \T_IS_NOT_IDENTICAL, 'content' => '!==']];
        foreach ($tokens as $index => $token) {
            $tokenId = $token->getId();
            if (isset($map[$tokenId])) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$map[$tokenId]['id'], $map[$tokenId]['content']]);
            }
        }
    }
}
