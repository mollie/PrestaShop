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
namespace MolliePrefix\PhpCsFixer\Fixer\ControlStructure;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Gregor Harlan <gharlan@web.de>
 */
final class NoUnneededControlParenthesesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    private static $loops = ['break' => ['lookupTokens' => \T_BREAK, 'neededSuccessors' => [';']], 'clone' => ['lookupTokens' => \T_CLONE, 'neededSuccessors' => [';', ':', ',', ')'], 'forbiddenContents' => ['?', ':']], 'continue' => ['lookupTokens' => \T_CONTINUE, 'neededSuccessors' => [';']], 'echo_print' => ['lookupTokens' => [\T_ECHO, \T_PRINT], 'neededSuccessors' => [';', [\T_CLOSE_TAG]]], 'return' => ['lookupTokens' => \T_RETURN, 'neededSuccessors' => [';', [\T_CLOSE_TAG]]], 'switch_case' => ['lookupTokens' => \T_CASE, 'neededSuccessors' => [';', ':']], 'yield' => ['lookupTokens' => \T_YIELD, 'neededSuccessors' => [';', ')']]];
    /**
     * Dynamic option set on constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // To be moved back to compile time property declaration when PHP support of PHP CS Fixer will be 7.0+
        if (\defined('T_COALESCE')) {
            self::$loops['clone']['forbiddenContents'][] = [\T_COALESCE, '??'];
        }
        if (\defined('T_YIELD_FROM')) {
            self::$loops['yield_from'] = ['lookupTokens' => \T_YIELD_FROM, 'neededSuccessors' => [';', ')']];
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $types = [];
        foreach (self::$loops as $loop) {
            $types[] = (array) $loop['lookupTokens'];
        }
        $types = \array_merge(...$types);
        return $tokens->isAnyTokenKindsFound($types);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Removes unneeded parentheses around control statements.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
while ($x) { while ($y) { break (2); } }
clone($a);
while ($y) { continue (2); }
echo("foo");
print("foo");
return (1 + 2);
switch ($a) { case($x); }
yield(2);
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
while ($x) { while ($y) { break (2); } }
clone($a);
while ($y) { continue (2); }
echo("foo");
print("foo");
return (1 + 2);
switch ($a) { case($x); }
yield(2);
', ['statements' => ['break', 'continue']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoTrailingWhitespaceFixer.
     */
    public function getPriority()
    {
        return 30;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        // Checks if specific statements are set and uses them in this case.
        $loops = \array_intersect_key(self::$loops, \array_flip($this->configuration['statements']));
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny(['(', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_BRACE_CLASS_INSTANTIATION_OPEN]])) {
                continue;
            }
            $blockStartIndex = $index;
            $index = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$index];
            foreach ($loops as $loop) {
                if (!$prevToken->isGivenKind($loop['lookupTokens'])) {
                    continue;
                }
                $blockEndIndex = $tokens->findBlockEnd($token->equals('(') ? \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE : \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_BRACE_CLASS_INSTANTIATION, $blockStartIndex);
                $blockEndNextIndex = $tokens->getNextMeaningfulToken($blockEndIndex);
                if (!$tokens[$blockEndNextIndex]->equalsAny($loop['neededSuccessors'])) {
                    continue;
                }
                if (\array_key_exists('forbiddenContents', $loop)) {
                    $forbiddenTokenIndex = $tokens->getNextTokenOfKind($blockStartIndex, $loop['forbiddenContents']);
                    // A forbidden token is found and is inside the parenthesis.
                    if (null !== $forbiddenTokenIndex && $forbiddenTokenIndex < $blockEndIndex) {
                        continue;
                    }
                }
                if ($tokens[$blockStartIndex - 1]->isWhitespace() || $tokens[$blockStartIndex - 1]->isComment()) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($blockStartIndex);
                } else {
                    // Adds a space to prevent broken code like `return2`.
                    $tokens[$blockStartIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
                }
                $tokens->clearTokenAndMergeSurroundingWhitespace($blockEndIndex);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('statements', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('statements', 'List of control statements to fix.'))->setAllowedTypes(['array'])->setDefault(['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield'])->getOption()], $this->getName());
    }
}
