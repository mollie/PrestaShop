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
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Generator\NamespacedStringTokenGenerator;
use MolliePrefix\PhpCsFixer\Tokenizer\Resolver\TypeShortNameResolver;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author VeeWee <toonverwerft@gmail.com>
 */
final class FullyQualifiedStrictTypesFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Transforms imported FQCN parameters and return types in function arguments to short version.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

use Foo\\Bar;

class SomeClass
{
    public function doSomething(\\Foo\\Bar $foo)
    {
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php

use Foo\\Bar;
use Foo\\Bar\\Baz;

class SomeClass
{
    public function doSomething(\\Foo\\Bar $foo): \\Foo\\Bar\\Baz
    {
    }
}
', new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70000))]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoSuperfluousPhpdocTagsFixer.
     * Must run after PhpdocToReturnTypeFixer.
     */
    public function getPriority()
    {
        return 7;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_FUNCTION) && (\count((new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer())->getDeclarations($tokens)) || \count((new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens)));
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $lastIndex = $tokens->count() - 1;
        for ($index = $lastIndex; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }
            // Return types are only available since PHP 7.0
            $this->fixFunctionReturnType($tokens, $index);
            $this->fixFunctionArguments($tokens, $index);
        }
    }
    /**
     * @param int $index
     */
    private function fixFunctionArguments(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $arguments = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer())->getFunctionArguments($tokens, $index);
        foreach ($arguments as $argument) {
            if (!$argument->hasTypeAnalysis()) {
                continue;
            }
            $this->detectAndReplaceTypeWithShortType($tokens, $argument->getTypeAnalysis());
        }
    }
    /**
     * @param int $index
     */
    private function fixFunctionReturnType(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        if (\PHP_VERSION_ID < 70000) {
            return;
        }
        $returnType = (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer())->getFunctionReturnType($tokens, $index);
        if (!$returnType) {
            return;
        }
        $this->detectAndReplaceTypeWithShortType($tokens, $returnType);
    }
    private function detectAndReplaceTypeWithShortType(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis $type)
    {
        if ($type->isReservedType()) {
            return;
        }
        $typeName = $type->getName();
        if (0 !== \strpos($typeName, '\\')) {
            return;
        }
        $shortType = (new \MolliePrefix\PhpCsFixer\Tokenizer\Resolver\TypeShortNameResolver())->resolve($tokens, $typeName);
        if ($shortType === $typeName) {
            return;
        }
        $shortType = (new \MolliePrefix\PhpCsFixer\Tokenizer\Generator\NamespacedStringTokenGenerator())->generate($shortType);
        if (\true === $type->isNullable()) {
            \array_unshift($shortType, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\MolliePrefix\PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, '?']));
        }
        $tokens->overrideRange($type->getStartIndex(), $type->getEndIndex(), $shortType);
    }
}
