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
namespace MolliePrefix\PhpCsFixer\Fixer\ClassNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
final class OrderedTraitsFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Trait `use` statements must be sorted alphabetically.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php class Foo { \nuse Z; use A; }\n")], null, 'Risky when depending on order of the imports.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT);
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($this->findUseStatementsGroups($tokens) as $uses) {
            $this->sortUseStatements($tokens, $uses);
        }
    }
    /**
     * @return iterable<array<int, Tokens>>
     */
    private function findUseStatementsGroups(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $uses = [];
        for ($index = 1, $max = \count($tokens); $index < $max; ++$index) {
            $token = $tokens[$index];
            if ($token->isWhitespace() || $token->isComment()) {
                continue;
            }
            if (!$token->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT)) {
                if (\count($uses) > 0) {
                    (yield $uses);
                    $uses = [];
                }
                continue;
            }
            $endIndex = $tokens->getNextTokenOfKind($index, [';', '{']);
            if ($tokens[$endIndex]->equals('{')) {
                $endIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $endIndex);
            }
            $use = [];
            for ($i = $index; $i <= $endIndex; ++$i) {
                $use[] = $tokens[$i];
            }
            $uses[$index] = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromArray($use);
            $index = $endIndex;
        }
    }
    /**
     * @param array<int, Tokens> $uses
     */
    private function sortUseStatements(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $uses)
    {
        foreach ($uses as $use) {
            $this->sortMultipleTraitsInStatement($use);
        }
        $this->sort($tokens, $uses);
    }
    private function sortMultipleTraitsInStatement(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $use)
    {
        $traits = [];
        $indexOfName = null;
        $name = [];
        for ($index = 0, $max = \count($use); $index < $max; ++$index) {
            $token = $use[$index];
            if ($token->isGivenKind([\T_STRING, \T_NS_SEPARATOR])) {
                $name[] = $token;
                if (null === $indexOfName) {
                    $indexOfName = $index;
                }
                continue;
            }
            if ($token->equalsAny([',', ';', '{'])) {
                $traits[$indexOfName] = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromArray($name);
                $name = [];
                $indexOfName = null;
            }
            if ($token->equals('{')) {
                $index = $use->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
            }
        }
        $this->sort($use, $traits);
    }
    /**
     * @param array<int, Tokens> $elements
     */
    private function sort(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, array $elements)
    {
        /**
         * @return string
         */
        $toTraitName = static function (\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $use) {
            $string = '';
            foreach ($use as $token) {
                if ($token->equalsAny([';', '{'])) {
                    break;
                }
                if ($token->isGivenKind([\T_NS_SEPARATOR, \T_STRING])) {
                    $string .= $token->getContent();
                }
            }
            return \ltrim($string, '\\');
        };
        $sortedElements = $elements;
        \uasort($sortedElements, static function (\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $useA, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $useB) use($toTraitName) {
            return \strcasecmp($toTraitName($useA), $toTraitName($useB));
        });
        $sortedElements = \array_combine(\array_keys($elements), \array_values($sortedElements));
        foreach (\array_reverse($sortedElements, \true) as $index => $tokensToInsert) {
            $tokens->overrideRange($index, $index + \count($elements[$index]) - 1, $tokensToInsert);
        }
    }
}
