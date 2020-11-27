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
namespace MolliePrefix\PhpCsFixer\Fixer\StringNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dave van der Brugge <dmvdbrugge@gmail.com>
 */
final class SimpleToComplexStringVariableFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Converts explicit variables in double-quoted strings and heredoc syntax from simple to complex format (`${` to `{$`).', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample(<<<'EOT'
<?php

namespace MolliePrefix;

$name = 'World';
echo "Hello {$name}!";

EOT
), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample(<<<'EOT'
<?php

namespace MolliePrefix;

$name = 'World';
echo <<<TEST
Hello {$name}!
TEST
;

EOT
)], "Doesn't touch implicit variables. Works together nicely with `explicit_string_variable`.");
    }
    /**
     * {@inheritdoc}
     *
     * Must run after ExplicitStringVariableFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOLLAR_OPEN_CURLY_BRACES);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = \count($tokens) - 3; $index > 0; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(\T_DOLLAR_OPEN_CURLY_BRACES)) {
                continue;
            }
            $varnameToken = $tokens[$index + 1];
            if (!$varnameToken->isGivenKind(\T_STRING_VARNAME)) {
                continue;
            }
            $dollarCloseToken = $tokens[$index + 2];
            if (!$dollarCloseToken->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DOLLAR_CLOSE_CURLY_BRACES)) {
                continue;
            }
            $tokenOfStringBeforeToken = $tokens[$index - 1];
            $stringContent = $tokenOfStringBeforeToken->getContent();
            if ('$' === \substr($stringContent, -1) && '\\$' !== \substr($stringContent, -2)) {
                $newContent = \substr($stringContent, 0, -1) . '\\$';
                $tokenOfStringBeforeToken = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_ENCAPSED_AND_WHITESPACE, $newContent]);
            }
            $tokens->overrideRange($index - 1, $index + 2, [$tokenOfStringBeforeToken, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_CURLY_OPEN, '{']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_VARIABLE, '$' . $varnameToken->getContent()]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CURLY_CLOSE, '}'])]);
        }
    }
}
