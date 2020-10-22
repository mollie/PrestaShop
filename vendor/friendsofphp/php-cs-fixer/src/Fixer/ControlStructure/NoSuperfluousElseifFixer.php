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

use MolliePrefix\PhpCsFixer\AbstractNoUselessElseFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
final class NoSuperfluousElseifFixer extends \MolliePrefix\PhpCsFixer\AbstractNoUselessElseFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_ELSE, \T_ELSEIF]);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Replaces superfluous `elseif` with `if`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nif (\$a) {\n    return 1;\n} elseif (\$b) {\n    return 2;\n}\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoAlternativeSyntaxFixer.
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if ($this->isElseif($tokens, $index) && $this->isSuperfluousElse($tokens, $index)) {
                $this->convertElseifToIf($tokens, $index);
            }
        }
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function isElseif(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if ($tokens[$index]->isGivenKind(\T_ELSEIF)) {
            return \true;
        }
        return $tokens[$index]->isGivenKind(\T_ELSE) && $tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(\T_IF);
    }
    /**
     * @param int $index
     */
    private function convertElseifToIf(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if ($tokens[$index]->isGivenKind(\T_ELSE)) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        } else {
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_IF, 'if']);
        }
        $whitespace = '';
        for ($previous = $index - 1; $previous > 0; --$previous) {
            $token = $tokens[$previous];
            if ($token->isWhitespace() && \MolliePrefix\PhpCsFixer\Preg::match('/(\\R\\N*)$/', $token->getContent(), $matches)) {
                $whitespace = $matches[1];
                break;
            }
        }
        if ('' === $whitespace) {
            return;
        }
        $previousToken = $tokens[$index - 1];
        if (!$previousToken->isWhitespace()) {
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $whitespace]));
        } elseif (!\MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $previousToken->getContent())) {
            $tokens[$index - 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $whitespace]);
        }
    }
}
