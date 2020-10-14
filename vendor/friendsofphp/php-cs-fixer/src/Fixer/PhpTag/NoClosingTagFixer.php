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
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for rules defined in PSR2 ¶2.2.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class NoClosingTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('The closing `?>` tag MUST be omitted from files containing only PHP.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nclass Sample\n{\n}\n?>\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_CLOSE_TAG);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (\count($tokens) < 2 || !$tokens->isMonolithicPhp() || !$tokens->isTokenKindFound(\T_CLOSE_TAG)) {
            return;
        }
        $closeTags = $tokens->findGivenKind(\T_CLOSE_TAG);
        $index = \key($closeTags);
        if (isset($tokens[$index - 1]) && $tokens[$index - 1]->isWhitespace()) {
            $tokens->clearAt($index - 1);
        }
        $tokens->clearAt($index);
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$prevIndex]->equalsAny([';', '}', [\T_OPEN_TAG]])) {
            $tokens->insertAt($prevIndex + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';'));
        }
    }
}
