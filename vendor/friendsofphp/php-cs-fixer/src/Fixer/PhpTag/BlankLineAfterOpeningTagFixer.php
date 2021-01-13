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
namespace MolliePrefix\PhpCsFixer\Fixer\PhpTag;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Ceeram <ceeram@cakephp.org>
 */
final class BlankLineAfterOpeningTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$a = 1;\n\$b = 1;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoBlankLinesBeforeNamespaceFixer.
     * Must run after DeclareStrictTypesFixer.
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
        return $tokens->isTokenKindFound(\T_OPEN_TAG);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        // ignore files with short open tag and ignore non-monolithic files
        if (!$tokens[0]->isGivenKind(\T_OPEN_TAG) || !$tokens->isMonolithicPhp()) {
            return;
        }
        $newlineFound = \false;
        /** @var Token $token */
        foreach ($tokens as $token) {
            if ($token->isWhitespace() && \false !== \strpos($token->getContent(), "\n")) {
                $newlineFound = \true;
                break;
            }
        }
        // ignore one-line files
        if (!$newlineFound) {
            return;
        }
        $token = $tokens[0];
        if (\false === \strpos($token->getContent(), "\n")) {
            $tokens[0] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \rtrim($token->getContent()) . $lineEnding]);
        }
        if (\false === \strpos($tokens[1]->getContent(), "\n")) {
            if ($tokens[1]->isWhitespace()) {
                $tokens[1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . $tokens[1]->getContent()]);
            } else {
                $tokens->insertAt(1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding]));
            }
        }
    }
}
