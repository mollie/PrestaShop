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
namespace MolliePrefix\PhpCsFixer\Fixer\FunctionNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Gregor Harlan
 */
final class UseArrowFunctionsFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Anonymous functions with one-liner return statement must use arrow functions.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace MolliePrefix;

\MolliePrefix\foo(function ($a) use($b) {
    return $a + $b;
});

SAMPLE
, new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70400))], null, 'Risky when using `isset()` on outside variables that are not imported with `use ()`.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70400 && $tokens->isAllTokenKindsFound([\T_FUNCTION, \T_RETURN]);
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
        $analyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION) || !$analyzer->isLambda($index)) {
                continue;
            }
            // Find parameters end
            // Abort if they are multilined
            $parametersStart = $tokens->getNextMeaningfulToken($index);
            if ($tokens[$parametersStart]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_RETURN_REF)) {
                $parametersStart = $tokens->getNextMeaningfulToken($parametersStart);
            }
            $parametersEnd = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $parametersStart);
            if ($this->isMultilined($tokens, $parametersStart, $parametersEnd)) {
                continue;
            }
            // Find `use ()` start and end
            // Abort if it contains reference variables
            $next = $tokens->getNextMeaningfulToken($parametersEnd);
            $useStart = null;
            $useEnd = null;
            if ($tokens[$next]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA)) {
                $useStart = $next;
                if ($tokens[$useStart - 1]->isGivenKind(\T_WHITESPACE)) {
                    --$useStart;
                }
                $next = $tokens->getNextMeaningfulToken($next);
                while (!$tokens[$next]->equals(')')) {
                    if ($tokens[$next]->equals('&')) {
                        // variables used by reference are not supported by arrow functions
                        continue 2;
                    }
                    $next = $tokens->getNextMeaningfulToken($next);
                }
                $useEnd = $next;
                $next = $tokens->getNextMeaningfulToken($next);
            }
            // Find opening brace and following `return`
            // Abort if there is more than whitespace between them (like comments)
            $braceOpen = $tokens[$next]->equals('{') ? $next : $tokens->getNextTokenOfKind($next, ['{']);
            $return = $braceOpen + 1;
            if ($tokens[$return]->isGivenKind(\T_WHITESPACE)) {
                ++$return;
            }
            if (!$tokens[$return]->isGivenKind(\T_RETURN)) {
                continue;
            }
            // Find semicolon of `return` statement
            $semicolon = $tokens->getNextTokenOfKind($return, ['{', ';']);
            if (!$tokens[$semicolon]->equals(';')) {
                continue;
            }
            // Find closing brace
            // Abort if there is more than whitespace between semicolon and closing brace
            $braceClose = $semicolon + 1;
            if ($tokens[$braceClose]->isGivenKind(\T_WHITESPACE)) {
                ++$braceClose;
            }
            if (!$tokens[$braceClose]->equals('}')) {
                continue;
            }
            // Abort if the `return` statement is multilined
            if ($this->isMultilined($tokens, $return, $semicolon)) {
                continue;
            }
            // Transform the function to an arrow function
            $this->transform($tokens, $index, $useStart, $useEnd, $braceOpen, $return, $semicolon, $braceClose);
        }
    }
    /**
     * @param int $start
     * @param int $end
     *
     * @return bool
     */
    private function isMultilined(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $start, $end)
    {
        for ($i = $start; $i < $end; ++$i) {
            if (\false !== \strpos($tokens[$i]->getContent(), "\n")) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param int      $index
     * @param null|int $useStart
     * @param null|int $useEnd
     * @param int      $braceOpen
     * @param int      $return
     * @param int      $semicolon
     * @param int      $braceClose
     */
    private function transform(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $useStart, $useEnd, $braceOpen, $return, $semicolon, $braceClose)
    {
        $tokens->clearRange($semicolon, $braceClose);
        $tokens->clearRange($braceOpen + 1, $return);
        $tokens[$braceOpen] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOUBLE_ARROW, '=>']);
        if ($useStart) {
            $tokens->clearRange($useStart, $useEnd);
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([T_FN, 'fn']);
    }
}
