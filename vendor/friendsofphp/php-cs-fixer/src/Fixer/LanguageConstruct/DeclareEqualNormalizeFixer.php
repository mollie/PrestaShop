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
namespace MolliePrefix\PhpCsFixer\Fixer\LanguageConstruct;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class DeclareEqualNormalizeFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @var string
     */
    private $callback;
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->callback = 'none' === $this->configuration['space'] ? 'removeWhitespaceAroundToken' : 'ensureWhitespaceAroundToken';
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Equal sign in declare statement should be surrounded by spaces or not following configuration.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\ndeclare(ticks =  1);\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\ndeclare(ticks=1);\n", ['space' => 'single'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after DeclareStrictTypesFixer.
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
        return $tokens->isTokenKindFound(\T_DECLARE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $callback = $this->callback;
        for ($index = 0, $count = $tokens->count(); $index < $count - 6; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_DECLARE)) {
                continue;
            }
            while (!$tokens[++$index]->equals('=')) {
            }
            $this->{$callback}($tokens, $index);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('space', 'Spacing to apply around the equal sign.'))->setAllowedValues(['single', 'none'])->setDefault('none')->getOption()]);
    }
    /**
     * @param int $index of `=` token
     */
    private function ensureWhitespaceAroundToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if ($tokens[$index + 1]->isWhitespace()) {
            if (' ' !== $tokens[$index + 1]->getContent()) {
                $tokens[$index + 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            }
        } else {
            $tokens->insertAt($index + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
        }
        if ($tokens[$index - 1]->isWhitespace()) {
            if (' ' !== $tokens[$index - 1]->getContent() && !$tokens[$tokens->getPrevNonWhitespace($index - 1)]->isComment()) {
                $tokens[$index - 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
            }
        } else {
            $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
        }
    }
    /**
     * @param int $index of `=` token
     */
    private function removeWhitespaceAroundToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (!$tokens[$tokens->getPrevNonWhitespace($index)]->isComment()) {
            $tokens->removeLeadingWhitespace($index);
        }
        $tokens->removeTrailingWhitespace($index);
    }
}
