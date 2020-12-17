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
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Volodymyr Kupriienko <vldmr.kuprienko@gmail.com>
 */
final class GroupImportFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('There MUST be group use for the same namespaces.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\nuse Foo\\Bar;\nuse Foo\\Baz;\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70000))]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID >= 70000 && $tokens->isTokenKindFound(\T_USE);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $useWithSameNamespaces = $this->getSameNamespaces($tokens);
        if ([] === $useWithSameNamespaces) {
            return;
        }
        $this->removeSingleUseStatements($useWithSameNamespaces, $tokens);
        $this->addGroupUseStatements($useWithSameNamespaces, $tokens);
    }
    /**
     * Gets namespace use analyzers with same namespaces.
     *
     * @return NamespaceUseAnalysis[]
     */
    private function getSameNamespaces(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $useDeclarations = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);
        if (0 === \count($useDeclarations)) {
            return [];
        }
        $allNamespaces = \array_map(function (\MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis $useDeclaration) {
            return $this->getNamespaceNameWithSlash($useDeclaration);
        }, $useDeclarations);
        $sameNamespaces = \array_filter(\array_count_values($allNamespaces), function ($count) {
            return $count > 1;
        });
        $sameNamespaces = \array_keys($sameNamespaces);
        $sameNamespaceAnalysis = \array_filter($useDeclarations, function ($useDeclaration) use($sameNamespaces) {
            $namespaceName = $this->getNamespaceNameWithSlash($useDeclaration);
            return \in_array($namespaceName, $sameNamespaces, \true);
        });
        \sort($sameNamespaceAnalysis);
        return $sameNamespaceAnalysis;
    }
    /**
     * @param NamespaceUseAnalysis[] $statements
     */
    private function removeSingleUseStatements(array $statements, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($statements as $useDeclaration) {
            $index = $useDeclaration->getStartIndex();
            $endIndex = $useDeclaration->getEndIndex();
            $useStatementTokens = [\T_USE, \T_WHITESPACE, \T_STRING, \T_NS_SEPARATOR, \T_AS, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONST_IMPORT, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_FUNCTION_IMPORT];
            while ($index !== $endIndex) {
                if ($tokens[$index]->isGivenKind($useStatementTokens)) {
                    $tokens->clearAt($index);
                }
                ++$index;
            }
            if (isset($tokens[$index]) && $tokens[$index]->equals(';')) {
                $tokens->clearAt($index);
            }
            ++$index;
            if (isset($tokens[$index]) && $tokens[$index]->isGivenKind(\T_WHITESPACE)) {
                $tokens->clearAt($index);
            }
        }
    }
    /**
     * @param NamespaceUseAnalysis[] $statements
     */
    private function addGroupUseStatements(array $statements, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $currentNamespace = '';
        $insertIndex = \array_slice($statements, -1)[0]->getEndIndex();
        while ($tokens[$insertIndex]->isGivenKind([\T_COMMENT, \T_DOC_COMMENT])) {
            ++$insertIndex;
        }
        foreach ($statements as $index => $useDeclaration) {
            $namespace = $this->getNamespaceNameWithSlash($useDeclaration);
            if ($currentNamespace !== $namespace) {
                if ($index > 1) {
                    ++$insertIndex;
                }
                $currentNamespace = $namespace;
                $insertIndex += $this->createNewGroup($tokens, $insertIndex, $useDeclaration, $currentNamespace);
            } else {
                $newTokens = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token(','), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])];
                if ($useDeclaration->isAliased()) {
                    $tokens->insertAt($insertIndex + 1, $newTokens);
                    $insertIndex += \count($newTokens);
                    $newTokens = [];
                    $insertIndex += $this->insertToGroupUseWithAlias($tokens, $insertIndex + 1, $useDeclaration);
                }
                $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $useDeclaration->getShortName()]);
                if (!isset($statements[$index + 1]) || $this->getNamespaceNameWithSlash($statements[$index + 1]) !== $currentNamespace) {
                    $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_CLOSE, '}']);
                    $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token(';');
                    $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, "\n"]);
                }
                $tokens->insertAt($insertIndex + 1, $newTokens);
                $insertIndex += \count($newTokens);
            }
        }
    }
    /**
     * @return string
     */
    private function getNamespaceNameWithSlash(\MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis $useDeclaration)
    {
        return \substr($useDeclaration->getFullName(), 0, \strripos($useDeclaration->getFullName(), '\\') + 1);
    }
    /**
     * Insert use with alias to the group.
     *
     * @param int $insertIndex
     *
     * @return int
     */
    private function insertToGroupUseWithAlias(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $insertIndex, \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis $useDeclaration)
    {
        $newTokens = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, \substr($useDeclaration->getFullName(), \strripos($useDeclaration->getFullName(), '\\') + 1)]), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_AS, 'as']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])];
        $tokens->insertAt($insertIndex, $newTokens);
        return \count($newTokens);
    }
    /**
     * Creates new use statement group.
     *
     * @param int    $insertIndex
     * @param string $currentNamespace
     *
     * @return int
     */
    private function createNewGroup(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $insertIndex, \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis $useDeclaration, $currentNamespace)
    {
        $insertedTokens = 0;
        if (\count($tokens) === $insertIndex) {
            $tokens->setSize($insertIndex + 1);
        }
        $newTokens = [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_USE, 'use']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])];
        if ($useDeclaration->isFunction() || $useDeclaration->isConstant()) {
            $importStatementParams = $useDeclaration->isFunction() ? [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_FUNCTION_IMPORT, 'function'] : [\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_CONST_IMPORT, 'const'];
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token($importStatementParams);
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']);
        }
        $namespaceParts = \array_filter(\explode('\\', $currentNamespace));
        foreach ($namespaceParts as $part) {
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $part]);
            $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_NS_SEPARATOR, '\\']);
        }
        $newTokens[] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_GROUP_IMPORT_BRACE_OPEN, '{']);
        $newTokensCount = \count($newTokens);
        $tokens->insertAt($insertIndex, $newTokens);
        $insertedTokens += $newTokensCount;
        $insertIndex += $newTokensCount;
        if ($useDeclaration->isAliased()) {
            $inserted = $this->insertToGroupUseWithAlias($tokens, $insertIndex + 1, $useDeclaration);
            $insertedTokens += $inserted;
            $insertIndex += $inserted;
        }
        $tokens->insertAt($insertIndex + 1, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $useDeclaration->getShortName()]));
        ++$insertedTokens;
        return $insertedTokens;
    }
}
