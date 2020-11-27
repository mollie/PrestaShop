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
namespace MolliePrefix\PhpCsFixer\Fixer\LanguageConstruct;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class ExplicitIndirectVariableFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Add curly braces to indirect variables to make them clear to understand. Requires PHP >= 7.0.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'EOT'
<?php

namespace MolliePrefix;

echo ${$foo};
echo ${$foo}['bar'];
echo $foo->{$bar}['baz'];
echo $foo->{$callback}($baz);

EOT
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70000))]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70000 && $tokens->isTokenKindFound(\T_VARIABLE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; $index > 1; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(\T_VARIABLE)) {
                continue;
            }
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$prevIndex];
            if (!$prevToken->equals('$') && !$prevToken->isGivenKind(\T_OBJECT_OPERATOR)) {
                continue;
            }
            $openingBrace = \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_VAR_BRACE_OPEN;
            $closingBrace = \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_VAR_BRACE_CLOSE;
            if ($prevToken->isGivenKind(\T_OBJECT_OPERATOR)) {
                $openingBrace = \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_PROP_BRACE_OPEN;
                $closingBrace = \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DYNAMIC_PROP_BRACE_CLOSE;
            }
            $tokens->overrideRange($index, $index, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$openingBrace, '{']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_VARIABLE, $token->getContent()]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$closingBrace, '}'])]);
        }
    }
}
