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
namespace MolliePrefix\PhpCsFixer\Fixer\Comment;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class MultilineCommentOpeningClosingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('DocBlocks must start with two asterisks, multiline comments must start with a single asterisk, after the opening slash. Both must end with a single asterisk before the closing slash.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample(<<<'EOT'
<?php

namespace MolliePrefix;

/******
 * Multiline comment with arbitrary asterisks count
 ******/
/**\
 * Multiline comment that seems a DocBlock
 */
/**
 * DocBlock with arbitrary asterisk count at the end
 **/

EOT
)]);
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
        foreach ($tokens as $index => $token) {
            $originalContent = $token->getContent();
            if (!$token->isGivenKind(\T_DOC_COMMENT) && !($token->isGivenKind(\T_COMMENT) && 0 === \strpos($originalContent, '/*'))) {
                continue;
            }
            $newContent = $originalContent;
            // Fix opening
            if ($token->isGivenKind(\T_COMMENT)) {
                $newContent = \MolliePrefix\PhpCsFixer\Preg::replace('/^\\/\\*{2,}(?!\\/)/', '/*', $newContent);
            }
            // Fix closing
            $newContent = \MolliePrefix\PhpCsFixer\Preg::replace('/(?<!\\/)\\*{2,}\\/$/', '*/', $newContent);
            if ($newContent !== $originalContent) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), $newContent]);
            }
        }
    }
}
