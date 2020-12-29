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
namespace MolliePrefix\PhpCsFixer\Fixer\Phpdoc;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for part of rule defined in PSR5 ¶7.22.
 *
 * @author SpacePossum
 */
final class PhpdocSingleLineVarSpacingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Single line `@var` PHPDoc should have proper spacing.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php /**@var   MyClass   \$a   */\n\$a = test();\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocNoAliasTagFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
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
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        /** @var Token $token */
        foreach ($tokens as $index => $token) {
            if (!$token->isComment()) {
                continue;
            }
            $content = $token->getContent();
            $fixedContent = $this->fixTokenContent($content);
            if ($content !== $fixedContent) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $fixedContent]);
            }
        }
    }
    /**
     * @param string $content
     *
     * @return string
     */
    private function fixTokenContent($content)
    {
        return \MolliePrefix\PhpCsFixer\Preg::replaceCallback('#^/\\*\\*\\h*@var\\h+(\\S+)\\h*(\\$\\S+)?\\h*([^\\n]*)\\*/$#', static function (array $matches) {
            $content = '/** @var';
            for ($i = 1, $m = \count($matches); $i < $m; ++$i) {
                if ('' !== $matches[$i]) {
                    $content .= ' ' . $matches[$i];
                }
            }
            return \rtrim($content) . ' */';
        }, $content);
    }
}
