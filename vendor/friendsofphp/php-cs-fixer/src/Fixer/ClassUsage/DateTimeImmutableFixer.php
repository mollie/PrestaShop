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
namespace MolliePrefix\PhpCsFixer\Fixer\ClassUsage;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class DateTimeImmutableFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Class `DateTimeImmutable` should be used instead of `DateTime`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nnew DateTime();\n")], null, 'Risky when the code relies on modifying `DateTime` objects or if any of the `date_create*` functions are overridden.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_STRING);
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
        $isInNamespace = \false;
        $isImported = \false;
        // e.g. use DateTime;
        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            $token = $tokens[$index];
            if ($token->isGivenKind(\T_NAMESPACE)) {
                $isInNamespace = \true;
                continue;
            }
            if ($token->isGivenKind(\T_USE) && $isInNamespace) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);
                if ('datetime' !== \strtolower($tokens[$nextIndex]->getContent())) {
                    continue;
                }
                $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);
                if ($tokens[$nextNextIndex]->equals(';')) {
                    $isImported = \true;
                }
                $index = $nextNextIndex;
                continue;
            }
            if (!$token->isGivenKind(\T_STRING)) {
                continue;
            }
            $lowercaseContent = \strtolower($token->getContent());
            if ('datetime' === $lowercaseContent) {
                $this->fixClassUsage($tokens, $index, $isInNamespace, $isImported);
                $limit = $tokens->count();
                // update limit, as fixing class usage may insert new token
            } elseif ('date_create' === $lowercaseContent) {
                $this->fixFunctionUsage($tokens, $index, 'date_create_immutable');
            } elseif ('date_create_from_format' === $lowercaseContent) {
                $this->fixFunctionUsage($tokens, $index, 'date_create_immutable_from_format');
            }
        }
    }
    /**
     * @param int  $index
     * @param bool $isInNamespace
     * @param bool $isImported
     */
    private function fixClassUsage(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $isInNamespace, $isImported)
    {
        $nextIndex = $tokens->getNextMeaningfulToken($index);
        if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            if ($tokens[$nextNextIndex]->isGivenKind(\T_STRING)) {
                $nextNextNextIndex = $tokens->getNextMeaningfulToken($nextNextIndex);
                if (!$tokens[$nextNextNextIndex]->equals('(')) {
                    return;
                }
            }
        }
        $isUsedAlone = \false;
        // e.g. new DateTime();
        $isUsedWithLeadingBackslash = \false;
        // e.g. new \DateTime();
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if (!$tokens[$prevPrevIndex]->isGivenKind(\T_STRING)) {
                $isUsedWithLeadingBackslash = \true;
            }
        } elseif (!$tokens[$prevIndex]->isGivenKind([\T_DOUBLE_COLON, \T_OBJECT_OPERATOR])) {
            $isUsedAlone = \true;
        }
        if ($isUsedWithLeadingBackslash || $isUsedAlone && ($isInNamespace && $isImported || !$isInNamespace)) {
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, \DateTimeImmutable::class]);
            if ($isInNamespace && $isUsedAlone) {
                $tokens->insertAt($index, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_NS_SEPARATOR, '\\']));
            }
        }
    }
    /**
     * @param int    $index
     * @param string $replacement
     */
    private function fixFunctionUsage(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $replacement)
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if ($tokens[$prevIndex]->isGivenKind([\T_DOUBLE_COLON, \T_NEW, \T_OBJECT_OPERATOR])) {
            return;
        }
        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if ($tokens[$prevPrevIndex]->isGivenKind([\T_NEW, \T_STRING])) {
                return;
            }
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $replacement]);
    }
}
