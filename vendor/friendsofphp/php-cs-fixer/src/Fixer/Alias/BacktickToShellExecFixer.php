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
namespace MolliePrefix\PhpCsFixer\Fixer\Alias;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class BacktickToShellExecFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound('`');
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Converts backtick operators to `shell_exec` calls.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample(<<<'EOT'
<?php

namespace MolliePrefix;

$plain = `ls -lah`;
$withVar = `ls -lah {$var1} {$var2} {$var3} {$var4[0]} {$var5->call()}`;

EOT
)], 'Conversion is done only when it is non risky, so when special chars like single-quotes, double-quotes and backticks are not used inside the command.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before EscapeImplicitBackslashesFixer, ExplicitStringVariableFixer, NativeFunctionInvocationFixer, SingleQuoteFixer.
     */
    public function getPriority()
    {
        return 2;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $backtickStarted = \false;
        $backtickTokens = [];
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            $token = $tokens[$index];
            if (!$token->equals('`')) {
                if ($backtickStarted) {
                    $backtickTokens[$index] = $token;
                }
                continue;
            }
            $backtickTokens[$index] = $token;
            if ($backtickStarted) {
                $this->fixBackticks($tokens, $backtickTokens);
                $backtickTokens = [];
            }
            $backtickStarted = !$backtickStarted;
        }
    }
    /**
     * Override backtick code with corresponding double-quoted string.
     */
    private function fixBackticks(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $backtickTokens)
    {
        // Track indexes for final override
        \ksort($backtickTokens);
        $openingBacktickIndex = \key($backtickTokens);
        \end($backtickTokens);
        $closingBacktickIndex = \key($backtickTokens);
        // Strip enclosing backticks
        \array_shift($backtickTokens);
        \array_pop($backtickTokens);
        // Double-quoted strings are parsed differently if they contain
        // variables or not, so we need to build the new token array accordingly
        $count = \count($backtickTokens);
        $newTokens = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'shell_exec']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token('(')];
        if (1 !== $count) {
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token('"');
        }
        foreach ($backtickTokens as $token) {
            if (!$token->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
                $newTokens[] = $token;
                continue;
            }
            $content = $token->getContent();
            // Escaping special chars depends on the context: too tricky
            if (\MolliePrefix\PhpCsFixer\Preg::match('/[`"\']/u', $content)) {
                return;
            }
            $kind = \T_ENCAPSED_AND_WHITESPACE;
            if (1 === $count) {
                $content = '"' . $content . '"';
                $kind = \T_CONSTANT_ENCAPSED_STRING;
            }
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$kind, $content]);
        }
        if (1 !== $count) {
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token('"');
        }
        $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(')');
        $tokens->overrideRange($openingBacktickIndex, $closingBacktickIndex, $newTokens);
    }
}
