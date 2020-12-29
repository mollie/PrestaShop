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
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class SimplifiedIfReturnFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    private $sequences = [['isNegative' => \false, 'sequence' => ['{', [\T_RETURN], [\T_STRING, 'true'], ';', '}', [\T_RETURN], [\T_STRING, 'false'], ';']], ['isNegative' => \true, 'sequence' => ['{', [\T_RETURN], [\T_STRING, 'false'], ';', '}', [\T_RETURN], [\T_STRING, 'true'], ';']], ['isNegative' => \false, 'sequence' => [[\T_RETURN], [\T_STRING, 'true'], ';', [\T_RETURN], [\T_STRING, 'false'], ';']], ['isNegative' => \true, 'sequence' => [[\T_RETURN], [\T_STRING, 'false'], ';', [\T_RETURN], [\T_STRING, 'true'], ';']]];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Simplify `if` control structures that return the boolean result of their condition.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nif (\$foo) { return true; } return false;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoMultilineWhitespaceBeforeSemicolonsFixer, NoSinglelineWhitespaceBeforeSemicolonsFixer.
     * Must run after NoSuperfluousElseifFixer, NoUnneededCurlyBracesFixer, NoUselessElseFixer, SemicolonAfterInstructionFixer.
     */
    public function getPriority()
    {
        return 1;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_IF, \T_RETURN, \T_STRING]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($ifIndex = $tokens->count() - 1; 0 <= $ifIndex; --$ifIndex) {
            $ifToken = $tokens[$ifIndex];
            if (!$ifToken->isGivenKind([\T_IF, \T_ELSEIF])) {
                continue;
            }
            $startParenthesisIndex = $tokens->getNextTokenOfKind($ifIndex, ['(']);
            $endParenthesisIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startParenthesisIndex);
            $firstCandidateIndex = $tokens->getNextMeaningfulToken($endParenthesisIndex);
            foreach ($this->sequences as $sequenceSpec) {
                $sequenceFound = $tokens->findSequence($sequenceSpec['sequence'], $firstCandidateIndex);
                if (null === $sequenceFound) {
                    continue;
                }
                $firstSequenceIndex = \key($sequenceFound);
                if ($firstSequenceIndex !== $firstCandidateIndex) {
                    continue;
                }
                $indexesToClear = \array_keys($sequenceFound);
                \array_pop($indexesToClear);
                // Preserve last semicolon
                \rsort($indexesToClear);
                foreach ($indexesToClear as $index) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                }
                $newTokens = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_RETURN, 'return']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])];
                if ($sequenceSpec['isNegative']) {
                    $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token('!');
                } else {
                    $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_BOOL_CAST, '(bool)']);
                }
                $tokens->overrideRange($ifIndex, $ifIndex, $newTokens);
            }
        }
    }
}
