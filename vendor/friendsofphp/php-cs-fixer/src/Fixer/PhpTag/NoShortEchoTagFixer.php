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
 * @author Vincent Klaiber <hello@vinkla.com>
 */
final class NoShortEchoTagFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Replace short-echo `<?=` with long format `<?php echo` syntax.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?= \"foo\";\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoMixedEchoPrintFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_OPEN_TAG_WITH_ECHO);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $i = \count($tokens);
        while ($i--) {
            $token = $tokens[$i];
            if (!$token->isGivenKind(\T_OPEN_TAG_WITH_ECHO)) {
                continue;
            }
            $nextIndex = $i + 1;
            $tokens[$i] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_OPEN_TAG, '<?php ']);
            if (!$tokens[$nextIndex]->isWhitespace()) {
                $tokens->insertAt($nextIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
            $tokens->insertAt($nextIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_ECHO, 'echo']));
        }
    }
}
