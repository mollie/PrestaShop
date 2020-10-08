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
final class LinebreakAfterOpeningTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Ensure there is no code on the same line as the PHP open tag.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$a = 1;\n\$b = 3;\n")]);
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
        // ignore files with short open tag and ignore non-monolithic files
        if (!$tokens[0]->isGivenKind(\T_OPEN_TAG) || !$tokens->isMonolithicPhp()) {
            return;
        }
        $newlineFound = \false;
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
        $tokens[0] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \rtrim($token->getContent()) . $this->whitespacesConfig->getLineEnding()]);
    }
}
