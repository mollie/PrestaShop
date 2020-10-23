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
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class NoSpacesAroundOffsetFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There MUST NOT be spaces around offset braces.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n", ['positions' => ['inside']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$sample = \$b [ 'a' ] [ 'b' ];\n", ['positions' => ['outside']])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(['[', \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny(['[', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_ARRAY_INDEX_CURLY_BRACE_OPEN]])) {
                continue;
            }
            if (\in_array('inside', $this->configuration['positions'], \true)) {
                if ($token->equals('[')) {
                    $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_INDEX_SQUARE_BRACE, $index);
                } else {
                    $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE, $index);
                }
                // remove space after opening `[` or `{`
                if ($tokens[$index + 1]->isWhitespace(" \t")) {
                    $tokens->clearAt($index + 1);
                }
                // remove space before closing `]` or `}`
                if ($tokens[$endIndex - 1]->isWhitespace(" \t")) {
                    $tokens->clearAt($endIndex - 1);
                }
            }
            if (\in_array('outside', $this->configuration['positions'], \true)) {
                $prevNonWhitespaceIndex = $tokens->getPrevNonWhitespace($index);
                if ($tokens[$prevNonWhitespaceIndex]->isComment()) {
                    continue;
                }
                $tokens->removeLeadingWhitespace($index);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        $values = ['inside', 'outside'];
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('positions', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('positions', 'Whether spacing should be fixed inside and/or outside the offset braces.'))->setAllowedTypes(['array'])->setAllowedValues([new \MolliePrefix\PhpCsFixer\FixerConfiguration\AllowedValueSubset($values)])->setDefault($values)->getOption()], $this->getName());
    }
}
