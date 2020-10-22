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
namespace MolliePrefix\PhpCsFixer\Fixer\FunctionNotation;

use MolliePrefix\PhpCsFixer\AbstractFopenFlagFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class FopenFlagsFixer extends \MolliePrefix\PhpCsFixer\AbstractFopenFlagFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('The flags in `fopen` calls must omit `t`, and `b` must be omitted or included consistently.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$a = fopen(\$foo, 'rwt');\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$a = fopen(\$foo, 'rwt');\n", ['b_mode' => \false])], null, 'Risky when the function `fopen` is overridden.');
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('b_mode', 'The `b` flag must be used (`true`) or omitted (`false`).'))->setAllowedTypes(['bool'])->setDefault(\true)->getOption()]);
    }
    /**
     * @param int $argumentStartIndex
     * @param int $argumentEndIndex
     */
    protected function fixFopenFlagToken(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $argumentStartIndex, $argumentEndIndex)
    {
        $argumentFlagIndex = null;
        for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
            if ($tokens[$i]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
                continue;
            }
            if (null !== $argumentFlagIndex) {
                return;
                // multiple meaningful tokens found, no candidate for fixing
            }
            $argumentFlagIndex = $i;
        }
        // check if second argument is candidate
        if (null === $argumentFlagIndex || !$tokens[$argumentFlagIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            return;
        }
        $content = $tokens[$argumentFlagIndex]->getContent();
        $contentQuote = $content[0];
        // `'`, `"`, `b` or `B`
        if ('b' === $contentQuote || 'B' === $contentQuote) {
            $binPrefix = $contentQuote;
            $contentQuote = $content[1];
            // `'` or `"`
            $mode = \substr($content, 2, -1);
        } else {
            $binPrefix = '';
            $mode = \substr($content, 1, -1);
        }
        if (\false === $this->isValidModeString($mode)) {
            return;
        }
        $mode = \str_replace('t', '', $mode);
        if ($this->configuration['b_mode']) {
            if (\false === \strpos($mode, 'b')) {
                $mode .= 'b';
            }
        } else {
            $mode = \str_replace('b', '', $mode);
        }
        $newContent = $binPrefix . $contentQuote . $mode . $contentQuote;
        if ($content !== $newContent) {
            $tokens[$argumentFlagIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_CONSTANT_ENCAPSED_STRING, $newContent]);
        }
    }
}
