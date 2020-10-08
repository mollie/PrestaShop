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
namespace MolliePrefix\PhpCsFixer\Fixer\Semicolon;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class SpaceAfterSemicolonFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Fix whitespace after a semicolon.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n                        sample();     \$test = 1;\n                        sample();\$test = 2;\n                        for ( ;;++\$sample) {\n                        }\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nfor (\$i = 0; ; ++\$i) {\n}\n", ['remove_in_empty_for_expressions' => \true])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after CombineConsecutiveUnsetsFixer, MultilineWhitespaceBeforeSemicolonsFixer, NoEmptyStatementFixer, OrderedClassElementsFixer, SingleImportPerStatementFixer, SingleTraitInsertPerStatementFixer.
     */
    public function getPriority()
    {
        return -1;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(';');
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('remove_in_empty_for_expressions', 'Whether spaces should be removed for empty `for` expressions.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $insideForParenthesesUntil = null;
        for ($index = 0, $max = \count($tokens) - 1; $index < $max; ++$index) {
            if ($this->configuration['remove_in_empty_for_expressions']) {
                if ($tokens[$index]->isGivenKind(\T_FOR)) {
                    $index = $tokens->getNextMeaningfulToken($index);
                    $insideForParenthesesUntil = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);
                    continue;
                }
                if ($index === $insideForParenthesesUntil) {
                    $insideForParenthesesUntil = null;
                    continue;
                }
            }
            if (!$tokens[$index]->equals(';')) {
                continue;
            }
            if (!$tokens[$index + 1]->isWhitespace()) {
                if (!$tokens[$index + 1]->equalsAny([')', [\T_INLINE_HTML]]) && (!$this->configuration['remove_in_empty_for_expressions'] || !$tokens[$index + 1]->equals(';'))) {
                    $tokens->insertAt($index + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
                    ++$max;
                }
                continue;
            }
            if (null !== $insideForParenthesesUntil && ($tokens[$index + 2]->equals(';') || $index + 2 === $insideForParenthesesUntil) && !\MolliePrefix\PhpCsFixer\Preg::match('/\\R/', $tokens[$index + 1]->getContent())) {
                $tokens->clearAt($index + 1);
                continue;
            }
            if (isset($tokens[$index + 2]) && !$tokens[$index + 1]->equals([\T_WHITESPACE, ' ']) && $tokens[$index + 1]->isWhitespace(" \t") && !$tokens[$index + 2]->isComment() && !$tokens[$index + 2]->equals(')')) {
                $tokens[$index + 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            }
        }
    }
}
