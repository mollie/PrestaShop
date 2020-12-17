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
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class NoUnneededCurlyBracesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Removes unneeded curly braces that are superfluous and aren\'t part of a control structure\'s body.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php {
    echo 1;
}

switch ($b) {
    case 1: {
        break;
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
namespace Foo {
    function Bar(){}
}
', ['namespaces' => \true])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoUselessElseFixer, NoUselessReturnFixer, ReturnAssignmentFixer, SimplifiedIfReturnFixer.
     */
    public function getPriority()
    {
        return 26;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound('}');
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($this->findCurlyBraceOpen($tokens) as $index) {
            if ($this->isOverComplete($tokens, $index)) {
                $this->clearOverCompleteBraces($tokens, $index, $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index));
            }
        }
        if ($this->configuration['namespaces']) {
            $this->clearIfIsOverCompleteNamespaceBlock($tokens);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('namespaces', 'Remove unneeded curly braces from bracketed namespaces.'))->setAllowedTypes(['bool'])->setDefault(\false)->getOption()]);
    }
    /**
     * @param int $openIndex  index of `{` token
     * @param int $closeIndex index of `}` token
     */
    private function clearOverCompleteBraces(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $openIndex, $closeIndex)
    {
        $tokens->clearTokenAndMergeSurroundingWhitespace($closeIndex);
        $tokens->clearTokenAndMergeSurroundingWhitespace($openIndex);
    }
    private function findCurlyBraceOpen(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($i = \count($tokens) - 1; $i > 0; --$i) {
            if ($tokens[$i]->equals('{')) {
                (yield $i);
            }
        }
    }
    /**
     * @param int $index index of `{` token
     *
     * @return bool
     */
    private function isOverComplete(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        static $include = ['{', '}', [\T_OPEN_TAG], ':', ';'];
        return $tokens[$tokens->getPrevMeaningfulToken($index)]->equalsAny($include);
    }
    private function clearIfIsOverCompleteNamespaceBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::isLegacyMode()) {
            $index = $tokens->getNextTokenOfKind(0, [[\T_NAMESPACE]]);
            $secondNamespaceIndex = $tokens->getNextTokenOfKind($index, [[\T_NAMESPACE]]);
            if (null !== $secondNamespaceIndex) {
                return;
            }
        } elseif (1 !== $tokens->countTokenKind(\T_NAMESPACE)) {
            return;
            // fast check, we never fix if multiple namespaces are defined
        }
        $index = $tokens->getNextTokenOfKind(0, [[\T_NAMESPACE]]);
        do {
            $index = $tokens->getNextMeaningfulToken($index);
        } while ($tokens[$index]->isGivenKind([\T_STRING, \T_NS_SEPARATOR]));
        if (!$tokens[$index]->equals('{')) {
            return;
            // `;`
        }
        $closeIndex = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
        $afterCloseIndex = $tokens->getNextMeaningfulToken($closeIndex);
        if (null !== $afterCloseIndex && (!$tokens[$afterCloseIndex]->isGivenKind(\T_CLOSE_TAG) || null !== $tokens->getNextMeaningfulToken($afterCloseIndex))) {
            return;
        }
        // clear up
        $tokens->clearTokenAndMergeSurroundingWhitespace($closeIndex);
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';');
        if ($tokens[$index - 1]->isWhitespace(" \t") && !$tokens[$index - 2]->isComment()) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index - 1);
        }
    }
}
