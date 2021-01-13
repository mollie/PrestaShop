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
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Gregor Harlan
 */
final class NoTrailingWhitespaceInStringFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_CONSTANT_ENCAPSED_STRING, \T_ENCAPSED_AND_WHITESPACE, \T_INLINE_HTML]);
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
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There must be no trailing whitespace in strings.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$a = '  \n    foo \n';\n")], null, 'Changing the whitespaces in strings might affect string comparisons and outputs.');
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1, $last = \true; $index >= 0; --$index, $last = \false) {
            /** @var Token $token */
            $token = $tokens[$index];
            if (!$token->isGivenKind([\T_CONSTANT_ENCAPSED_STRING, \T_ENCAPSED_AND_WHITESPACE, \T_INLINE_HTML])) {
                continue;
            }
            $isInlineHtml = $token->isGivenKind(\T_INLINE_HTML);
            $regex = $isInlineHtml && $last ? '/\\h+(?=\\R|$)/' : '/\\h+(?=\\R)/';
            $content = \MolliePrefix\PhpCsFixer\Preg::replace($regex, '', $token->getContent());
            if ($token->getContent() === $content) {
                continue;
            }
            if (!$isInlineHtml || 0 === $index) {
                $this->updateContent($tokens, $index, $content);
                continue;
            }
            $prev = $index - 1;
            if ($tokens[$prev]->equals([\T_CLOSE_TAG, '?>']) && \MolliePrefix\PhpCsFixer\Preg::match('/^\\R/', $content, $match)) {
                $tokens[$prev] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_CLOSE_TAG, $tokens[$prev]->getContent() . $match[0]]);
                $content = \substr($content, \strlen($match[0]));
                $content = \false === $content ? '' : $content;
            }
            $this->updateContent($tokens, $index, $content);
        }
    }
    /**
     * @param int    $index
     * @param string $content
     */
    private function updateContent(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $content)
    {
        if ('' === $content) {
            $tokens->clearAt($index);
            return;
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[$index]->getId(), $content]);
    }
}
