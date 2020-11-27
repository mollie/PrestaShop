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
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for rules defined in PSR2 ¶2.4.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class IndentationTypeFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * @var string
     */
    private $indent;
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Code MUST use configured indentation type.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\nif (true) {\n\techo 'Hello!';\n}\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocIndentFixer.
     * Must run after ClassAttributesSeparationFixer, MethodSeparationFixer.
     */
    public function getPriority()
    {
        return 50;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT, \T_WHITESPACE]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $this->indent = $this->whitespacesConfig->getIndent();
        foreach ($tokens as $index => $token) {
            if ($token->isComment()) {
                $tokens[$index] = $this->fixIndentInComment($tokens, $index);
                continue;
            }
            if ($token->isWhitespace()) {
                $tokens[$index] = $this->fixIndentToken($tokens, $index);
                continue;
            }
        }
    }
    /**
     * @param int $index
     *
     * @return Token
     */
    private function fixIndentInComment(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $content = \MolliePrefix\PhpCsFixer\Preg::replace('/^(?:(?<! ) {1,3})?\\t/m', '\\1    ', $tokens[$index]->getContent(), -1, $count);
        // Also check for more tabs.
        while (0 !== $count) {
            $content = \MolliePrefix\PhpCsFixer\Preg::replace('/^(\\ +)?\\t/m', '\\1    ', $content, -1, $count);
        }
        $indent = $this->indent;
        // change indent to expected one
        $content = \MolliePrefix\PhpCsFixer\Preg::replaceCallback('/^(?:    )+/m', function ($matches) use($indent) {
            return $this->getExpectedIndent($matches[0], $indent);
        }, $content);
        return new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$tokens[$index]->getId(), $content]);
    }
    /**
     * @param int $index
     *
     * @return Token
     */
    private function fixIndentToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $content = $tokens[$index]->getContent();
        $previousTokenHasTrailingLinebreak = \false;
        // @TODO 3.0 this can be removed when we have a transformer for "T_OPEN_TAG" to "T_OPEN_TAG + T_WHITESPACE"
        if (\false !== \strpos($tokens[$index - 1]->getContent(), "\n")) {
            $content = "\n" . $content;
            $previousTokenHasTrailingLinebreak = \true;
        }
        $indent = $this->indent;
        $newContent = \MolliePrefix\PhpCsFixer\Preg::replaceCallback(
            '/(\\R)(\\h+)/',
            // find indent
            function (array $matches) use($indent) {
                // normalize mixed indent
                $content = \MolliePrefix\PhpCsFixer\Preg::replace('/(?:(?<! ) {1,3})?\\t/', '    ', $matches[2]);
                // change indent to expected one
                return $matches[1] . $this->getExpectedIndent($content, $indent);
            },
            $content
        );
        if ($previousTokenHasTrailingLinebreak) {
            $newContent = \substr($newContent, 1);
        }
        return new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $newContent]);
    }
    /**
     * @param string $content
     * @param string $indent
     *
     * @return string mixed
     */
    private function getExpectedIndent($content, $indent)
    {
        if ("\t" === $indent) {
            $content = \str_replace('    ', $indent, $content);
        }
        return $content;
    }
}
