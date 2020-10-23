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
namespace MolliePrefix\PhpCsFixer\Fixer\CastNotation;

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
 */
final class CastSpacesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @internal
     */
    const INSIDE_CAST_SPACE_REPLACE_MAP = [' ' => '', "\t" => '', "\n" => '', "\r" => '', "\0" => '', "\v" => ''];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('A single space or none should be between cast and variable.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$bar = ( string )  \$a;\n\$foo = (int)\$b;\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$bar = ( string )  \$a;\n\$foo = (int)\$b;\n", ['space' => 'single']), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\n\$bar = ( string )  \$a;\n\$foo = (int) \$b;\n", ['space' => 'none'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoShortBoolCastFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(\MolliePrefix\PhpCsFixer\Tokenizer\Token::getCastTokenKinds());
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isCast()) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), \strtr($token->getContent(), self::INSIDE_CAST_SPACE_REPLACE_MAP)]);
            if ('single' === $this->configuration['space']) {
                // force single whitespace after cast token:
                if ($tokens[$index + 1]->isWhitespace(" \t")) {
                    // - if next token is whitespaces that contains only spaces and tabs - override next token with single space
                    $tokens[$index + 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                } elseif (!$tokens[$index + 1]->isWhitespace()) {
                    // - if next token is not whitespaces that contains spaces, tabs and new lines - append single space to current token
                    $tokens->insertAt($index + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
                }
                continue;
            }
            // force no whitespace after cast token:
            if ($tokens[$index + 1]->isWhitespace()) {
                $tokens->clearAt($index + 1);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('space', 'spacing to apply between cast and variable.'))->setAllowedValues(['none', 'single'])->setDefault('single')->getOption()]);
    }
}
