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
namespace MolliePrefix\PhpCsFixer\Fixer\Import;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Carlos Cirello <carlos.cirello.nl@gmail.com>
 */
final class NoLeadingImportSlashFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Remove leading slashes in `use` clauses.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nnamespace Foo;\nuse \\Bar;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before OrderedImportsFixer.
     * Must run after NoUnusedImportsFixer, SingleImportPerStatementFixer.
     */
    public function getPriority()
    {
        return -20;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_USE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $usesIndexes = $tokensAnalyzer->getImportUseIndexes();
        foreach ($usesIndexes as $idx) {
            $nextTokenIdx = $tokens->getNextMeaningfulToken($idx);
            $nextToken = $tokens[$nextTokenIdx];
            if ($nextToken->isGivenKind(\T_NS_SEPARATOR)) {
                $this->removeLeadingImportSlash($tokens, $nextTokenIdx);
            } elseif ($nextToken->isGivenKind([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_FUNCTION_IMPORT, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONST_IMPORT])) {
                $nextTokenIdx = $tokens->getNextMeaningfulToken($nextTokenIdx);
                if ($tokens[$nextTokenIdx]->isGivenKind(\T_NS_SEPARATOR)) {
                    $this->removeLeadingImportSlash($tokens, $nextTokenIdx);
                }
            }
        }
    }
    /**
     * @param int $index
     */
    private function removeLeadingImportSlash(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $previousIndex = $tokens->getPrevNonWhitespace($index);
        if ($previousIndex < $index - 1 || $tokens[$previousIndex]->isComment()) {
            $tokens->clearAt($index);
            return;
        }
        $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
    }
}
