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
namespace MolliePrefix\PhpCsFixer\Fixer\Whitespace;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Jack Cherng <jfcherng@gmail.com>
 */
final class CompactNullableTypehintFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Remove extra spaces in a nullable typehint.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\nfunction sample(? string \$str): ? string\n{}\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100))], 'Rule is applied only in a PHP 7.1+ environment.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70100 && $tokens->isTokenKindFound(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        static $typehintKinds = [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_TYPEHINT, \T_CALLABLE, \T_NS_SEPARATOR, \T_STRING];
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE)) {
                continue;
            }
            // remove whitespaces only if there are only whitespaces
            // between '?' and the variable type
            if ($tokens[$index + 1]->isWhitespace() && $tokens[$index + 2]->isGivenKind($typehintKinds)) {
                $tokens->removeTrailingWhitespace($index);
            }
        }
    }
}
