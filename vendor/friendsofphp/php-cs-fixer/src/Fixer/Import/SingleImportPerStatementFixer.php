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
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * Fixer for rules defined in PSR2 ¶3.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author SpacePossum
 */
final class SingleImportPerStatementFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There MUST be one use keyword per declaration.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nuse Foo, Sample, Sample\\Sample as Sample2;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before MultilineWhitespaceBeforeSemicolonsFixer, NoLeadingImportSlashFixer, NoSinglelineWhitespaceBeforeSemicolonsFixer, NoUnusedImportsFixer, SpaceAfterSemicolonFixer.
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
        return $tokens->isTokenKindFound(\T_USE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $uses = \array_reverse($tokensAnalyzer->getImportUseIndexes());
        foreach ($uses as $index) {
            $endIndex = $tokens->getNextTokenOfKind($index, [';', [\T_CLOSE_TAG]]);
            $groupClose = $tokens->getPrevMeaningfulToken($endIndex);
            if ($tokens[$groupClose]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_CLOSE)) {
                $this->fixGroupUse($tokens, $index, $endIndex);
            } else {
                $this->fixMultipleUse($tokens, $index, $endIndex);
            }
        }
    }
    /**
     * @param int $index
     *
     * @return array
     */
    private function getGroupDeclaration(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $groupPrefix = '';
        $comment = '';
        $groupOpenIndex = null;
        for ($i = $index + 1;; ++$i) {
            if ($tokens[$i]->isGivenKind(\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_OPEN)) {
                $groupOpenIndex = $i;
                break;
            }
            if ($tokens[$i]->isComment()) {
                $comment .= $tokens[$i]->getContent();
                if (!$tokens[$i - 1]->isWhitespace() && !$tokens[$i + 1]->isWhitespace()) {
                    $groupPrefix .= ' ';
                }
                continue;
            }
            if ($tokens[$i]->isWhitespace()) {
                $groupPrefix .= ' ';
                continue;
            }
            $groupPrefix .= $tokens[$i]->getContent();
        }
        return [\rtrim($groupPrefix), $groupOpenIndex, $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_GROUP_IMPORT_BRACE, $groupOpenIndex), $comment];
    }
    /**
     * @param string $groupPrefix
     * @param int    $groupOpenIndex
     * @param int    $groupCloseIndex
     * @param string $comment
     *
     * @return string[]
     */
    private function getGroupStatements(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $groupPrefix, $groupOpenIndex, $groupCloseIndex, $comment)
    {
        $statements = [];
        $statement = $groupPrefix;
        for ($i = $groupOpenIndex + 1; $i <= $groupCloseIndex; ++$i) {
            $token = $tokens[$i];
            if ($token->equals(',') && $tokens[$tokens->getNextMeaningfulToken($i)]->equals([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_CLOSE])) {
                continue;
            }
            if ($token->equalsAny([',', [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_CLOSE]])) {
                $statements[] = 'use' . $statement . ';';
                $statement = $groupPrefix;
                continue;
            }
            if ($token->isWhitespace()) {
                $j = $tokens->getNextMeaningfulToken($i);
                if ($tokens[$j]->equals([\T_AS])) {
                    $statement .= ' as ';
                    $i += 2;
                } elseif ($tokens[$j]->equals([\T_FUNCTION])) {
                    $statement = ' function' . $statement;
                    $i += 2;
                } elseif ($tokens[$j]->equals([\T_CONST])) {
                    $statement = ' const' . $statement;
                    $i += 2;
                }
                if ($token->isWhitespace(" \t") || '//' !== \substr($tokens[$i - 1]->getContent(), 0, 2)) {
                    continue;
                }
            }
            $statement .= $token->getContent();
        }
        if ('' !== $comment) {
            $statements[0] .= ' ' . $comment;
        }
        return $statements;
    }
    /**
     * @param int $index
     * @param int $endIndex
     */
    private function fixGroupUse(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $endIndex)
    {
        list($groupPrefix, $groupOpenIndex, $groupCloseIndex, $comment) = $this->getGroupDeclaration($tokens, $index);
        $statements = $this->getGroupStatements($tokens, $groupPrefix, $groupOpenIndex, $groupCloseIndex, $comment);
        if (\count($statements) < 2) {
            return;
        }
        $tokens->clearRange($index, $groupCloseIndex);
        if ($tokens[$endIndex]->equals(';')) {
            $tokens->clearAt($endIndex);
        }
        $ending = $this->whitespacesConfig->getLineEnding();
        $importTokens = \MolliePrefix\PhpCsFixer\Tokenizer\Tokens::fromCode('<?php ' . \implode($ending, $statements));
        $importTokens->clearAt(0);
        $importTokens->clearEmptyTokens();
        $tokens->insertAt($index, $importTokens);
    }
    /**
     * @param int $index
     * @param int $endIndex
     */
    private function fixMultipleUse(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index, $endIndex)
    {
        $ending = $this->whitespacesConfig->getLineEnding();
        for ($i = $endIndex - 1; $i > $index; --$i) {
            if (!$tokens[$i]->equals(',')) {
                continue;
            }
            $tokens[$i] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';');
            $i = $tokens->getNextMeaningfulToken($i);
            $tokens->insertAt($i, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_USE, 'use']));
            $tokens->insertAt($i + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            $indent = \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer::detectIndent($tokens, $index);
            if ($tokens[$i - 1]->isWhitespace()) {
                $tokens[$i - 1] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $ending . $indent]);
                continue;
            }
            if (\false === \strpos($tokens[$i - 1]->getContent(), "\n")) {
                $tokens->insertAt($i, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $ending . $indent]));
            }
        }
    }
}
