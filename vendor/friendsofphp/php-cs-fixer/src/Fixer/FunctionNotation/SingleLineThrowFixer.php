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
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class SingleLineThrowFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * @internal
     */
    const REMOVE_WHITESPACE_AFTER_TOKENS = ['['];
    /**
     * @internal
     */
    const REMOVE_WHITESPACE_AROUND_TOKENS = ['(', [\T_OBJECT_OPERATOR], [\T_DOUBLE_COLON]];
    /**
     * @internal
     */
    const REMOVE_WHITESPACE_BEFORE_TOKENS = [')', ']', ',', ';'];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Throwing exception must be done in single line.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nthrow new Exception(\n    'Error.',\n    500\n);\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_THROW);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before ConcatSpaceFixer.
     */
    public function getPriority()
    {
        // must be fun before ConcatSpaceFixer
        return 1;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_THROW)) {
                continue;
            }
            /** @var int $openingBraceCandidateIndex */
            $openingBraceCandidateIndex = $tokens->getNextTokenOfKind($index, [';', '(']);
            while ($tokens[$openingBraceCandidateIndex]->equals('(')) {
                /** @var int $closingBraceIndex */
                $closingBraceIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingBraceCandidateIndex);
                /** @var int $openingBraceCandidateIndex */
                $openingBraceCandidateIndex = $tokens->getNextTokenOfKind($closingBraceIndex, [';', '(']);
            }
            $this->trimNewLines($tokens, $index, $openingBraceCandidateIndex);
        }
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function trimNewLines(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        for ($index = $startIndex; $index < $endIndex; ++$index) {
            $content = $tokens[$index]->getContent();
            if ($tokens[$index]->isGivenKind(\T_COMMENT)) {
                if (0 === \strpos($content, '//')) {
                    $content = '/*' . \substr($content, 2) . ' */';
                    $tokens->clearAt($index + 1);
                } elseif (0 === \strpos($content, '#')) {
                    $content = '/*' . \substr($content, 1) . ' */';
                    $tokens->clearAt($index + 1);
                } elseif (\false !== \MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $content)) {
                    $content = \MolliePrefix\PhpCsFixer\Preg::replace('/\\R/', ' ', $content);
                }
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_COMMENT, $content]);
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_WHITESPACE)) {
                continue;
            }
            if (0 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $content)) {
                continue;
            }
            $prevIndex = $tokens->getNonEmptySibling($index, -1);
            if ($tokens[$prevIndex]->equalsAny(\array_merge(self::REMOVE_WHITESPACE_AFTER_TOKENS, self::REMOVE_WHITESPACE_AROUND_TOKENS))) {
                $tokens->clearAt($index);
                continue;
            }
            $nextIndex = $tokens->getNonEmptySibling($index, 1);
            if ($tokens[$nextIndex]->equalsAny(\array_merge(self::REMOVE_WHITESPACE_AROUND_TOKENS, self::REMOVE_WHITESPACE_BEFORE_TOKENS))) {
                if (!$tokens[$prevIndex]->isGivenKind(\T_FUNCTION)) {
                    $tokens->clearAt($index);
                    continue;
                }
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
        }
    }
}
