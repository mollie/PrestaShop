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
namespace MolliePrefix\PhpCsFixer\Fixer\CastNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class ShortScalarCastFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Cast `(boolean)` and `(integer)` should be written as `(bool)` and `(int)`, `(double)` and `(real)` as `(float)`, `(binary)` as `(string)`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\n\$a = (boolean) \$b;\n\$a = (integer) \$b;\n\$a = (double) \$b;\n\$a = (real) \$b;\n\n\$a = (binary) \$b;\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(null, 70399)), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\n\$a = (boolean) \$b;\n\$a = (integer) \$b;\n\$a = (double) \$b;\n\n\$a = (binary) \$b;\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70400))]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(\MolliePrefix\PhpCsFixer\Tokenizer\Token::getCastTokenKinds());
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        static $castMap = ['boolean' => 'bool', 'integer' => 'int', 'double' => 'float', 'real' => 'float', 'binary' => 'string'];
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if (!$tokens[$index]->isCast()) {
                continue;
            }
            $castFrom = \trim(\substr($tokens[$index]->getContent(), 1, -1));
            $castFromLowered = \strtolower($castFrom);
            if (!\array_key_exists($castFromLowered, $castMap)) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[$index]->getId(), \str_replace($castFrom, $castMap[$castFromLowered], $tokens[$index]->getContent())]);
        }
    }
}
