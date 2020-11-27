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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 * @author Julien Falque <julien.falque@gmail.com>
 */
final class AlignMultilineCommentFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    private $tokenKinds;
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->tokenKinds = [\T_DOC_COMMENT];
        if ('phpdocs_only' !== $this->configuration['comment_type']) {
            $this->tokenKinds[] = \T_COMMENT;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
    /**
            * This is a DOC Comment
with a line not prefixed with asterisk

   */
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
    /*
            * This is a doc-like multiline comment
*/
', ['comment_type' => 'phpdocs_like']), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
    /*
            * This is a doc-like multiline comment
with a line not prefixed with asterisk

   */
', ['comment_type' => 'all_multiline'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocTrimConsecutiveBlankLineSeparationFixer.
     * Must run after ArrayIndentationFixer.
     */
    public function getPriority()
    {
        return -40;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound($this->tokenKinds);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind($this->tokenKinds)) {
                continue;
            }
            $whitespace = '';
            $previousIndex = $index - 1;
            if ($tokens[$previousIndex]->isWhitespace()) {
                $whitespace = $tokens[$previousIndex]->getContent();
                --$previousIndex;
            }
            if ($tokens[$previousIndex]->isGivenKind(\T_OPEN_TAG)) {
                $whitespace = \MolliePrefix\PhpCsFixer\Preg::replace('/\\S/', '', $tokens[$previousIndex]->getContent()) . $whitespace;
            }
            if (1 !== \MolliePrefix\PhpCsFixer\Preg::match('/\\R(\\h*)$/', $whitespace, $matches)) {
                continue;
            }
            if ($token->isGivenKind(\T_COMMENT) && 'all_multiline' !== $this->configuration['comment_type'] && 1 === \MolliePrefix\PhpCsFixer\Preg::match('/\\R(?:\\R|\\s*[^\\s\\*])/', $token->getContent())) {
                continue;
            }
            $indentation = $matches[1];
            $lines = \MolliePrefix\PhpCsFixer\Preg::split('/\\R/u', $token->getContent());
            foreach ($lines as $lineNumber => $line) {
                if (0 === $lineNumber) {
                    continue;
                }
                $line = \ltrim($line);
                if ($token->isGivenKind(\T_COMMENT) && (!isset($line[0]) || '*' !== $line[0])) {
                    continue;
                }
                if (!isset($line[0])) {
                    $line = '*';
                } elseif ('*' !== $line[0]) {
                    $line = '* ' . $line;
                }
                $lines[$lineNumber] = $indentation . ' ' . $line;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \implode($lineEnding, $lines)]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('comment_type', 'Whether to fix PHPDoc comments only (`phpdocs_only`), any multi-line comment whose lines all start with an asterisk (`phpdocs_like`) or any multi-line comment (`all_multiline`).'))->setAllowedValues(['phpdocs_only', 'phpdocs_like', 'all_multiline'])->setDefault('phpdocs_only')->getOption()]);
    }
}
