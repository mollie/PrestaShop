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
 * Fixer for rules defined in PSR2 ¶2.2.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author SpacePossum
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class LineEndingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('All PHP files must use same line ending.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php \$b = \" \$a \r\n 123\"; \$a = <<<TEST\r\nAAAAA \r\n |\r\nTEST;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $ending = $this->whitespacesConfig->getLineEnding();
        for ($index = 0, $count = \count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if ($token->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
                if ($tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(\T_END_HEREDOC)) {
                    $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \MolliePrefix\PhpCsFixer\Preg::replace('#\\R#', $ending, $token->getContent())]);
                }
                continue;
            }
            if ($token->isGivenKind([\T_CLOSE_TAG, \T_COMMENT, \T_DOC_COMMENT, \T_OPEN_TAG, \T_START_HEREDOC, \T_WHITESPACE])) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \MolliePrefix\PhpCsFixer\Preg::replace('#\\R#', $ending, $token->getContent())]);
            }
        }
    }
}
