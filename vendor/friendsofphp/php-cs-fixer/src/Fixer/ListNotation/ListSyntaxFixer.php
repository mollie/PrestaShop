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
namespace MolliePrefix\PhpCsFixer\Fixer\ListNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class ListSyntaxFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    private $candidateTokenKind;
    /**
     * Use 'syntax' => 'long'|'short'.
     *
     * @param null|array<string, string> $configuration
     *
     * @throws InvalidFixerConfigurationException
     */
    public function configure(array $configuration = null)
    {
        parent::configure($configuration);
        $this->candidateTokenKind = 'long' === $this->configuration['syntax'] ? \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN : \T_LIST;
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('List (`array` destructuring) assignment should be declared using the configured syntax. Requires PHP >= 7.1.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\n[\$sample] = \$array;\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100)), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\nlist(\$sample) = \$array;\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100), ['syntax' => 'short'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer, TernaryOperatorSpacesFixer.
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
        return \PHP_VERSION_ID >= 70100 && $tokens->isTokenKindFound($this->candidateTokenKind);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            if ($tokens[$index]->isGivenKind($this->candidateTokenKind)) {
                if (\T_LIST === $this->candidateTokenKind) {
                    $this->fixToShortSyntax($tokens, $index);
                } else {
                    $this->fixToLongSyntax($tokens, $index);
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('syntax', 'Whether to use the `long` or `short` `list` syntax.'))->setAllowedValues(['long', 'short'])->setDefault('long')->getOption()]);
    }
    /**
     * @param int $index
     */
    private function fixToLongSyntax(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        static $typesOfInterest = [[\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE], '['];
        $closeIndex = $tokens->getNextTokenOfKind($index, $typesOfInterest);
        if (!$tokens[$closeIndex]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE)) {
            return;
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token('(');
        $tokens[$closeIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(')');
        $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_LIST, 'list']));
    }
    /**
     * @param int $index
     */
    private function fixToShortSyntax(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $openIndex = $tokens->getNextTokenOfKind($index, ['(']);
        $closeIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);
        $tokens[$openIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN, '[']);
        $tokens[$closeIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE, ']']);
        $tokens->clearTokenAndMergeSurroundingWhitespace($index);
    }
}
