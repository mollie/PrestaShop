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
namespace MolliePrefix\PhpCsFixer\Tokenizer\Resolver;

use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @internal
 */
final class TypeShortNameResolver
{
    /**
     * This method will resolve the shortName of a FQCN if possible or otherwise return the inserted type name.
     * E.g.: use Foo\Bar => "Bar".
     *
     * @param string $typeName
     *
     * @return string
     */
    public function resolve(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $typeName)
    {
        // First match explicit imports:
        $useMap = $this->getUseMapFromTokens($tokens);
        foreach ($useMap as $shortName => $fullName) {
            $regex = '/^\\\\?' . \preg_quote($fullName, '/') . '$/';
            if (\MolliePrefix\PhpCsFixer\Preg::match($regex, $typeName)) {
                return $shortName;
            }
        }
        // Next try to match (partial) classes inside the same namespace
        // For now only support one namespace per file:
        $namespaces = $this->getNamespacesFromTokens($tokens);
        if (1 === \count($namespaces)) {
            foreach ($namespaces as $fullName) {
                $matches = [];
                $regex = '/^\\\\?' . \preg_quote($fullName, '/') . '\\\\(?P<className>.+)$/';
                if (\MolliePrefix\PhpCsFixer\Preg::match($regex, $typeName, $matches)) {
                    return $matches['className'];
                }
            }
        }
        // Next: Try to match partial use statements:
        foreach ($useMap as $shortName => $fullName) {
            $matches = [];
            $regex = '/^\\\\?' . \preg_quote($fullName, '/') . '\\\\(?P<className>.+)$/';
            if (\MolliePrefix\PhpCsFixer\Preg::match($regex, $typeName, $matches)) {
                return $shortName . '\\' . $matches['className'];
            }
        }
        return $typeName;
    }
    /**
     * @return array<string, string> A list of all FQN namespaces in the file with the short name as key
     */
    private function getNamespacesFromTokens(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \array_map(static function (\MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis $info) {
            return $info->getFullName();
        }, (new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer())->getDeclarations($tokens));
    }
    /**
     * @return array<string, string> A list of all FQN use statements in the file with the short name as key
     */
    private function getUseMapFromTokens(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $map = [];
        foreach ((new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens) as $useDeclaration) {
            $map[$useDeclaration->getShortName()] = $useDeclaration->getFullName();
        }
        return $map;
    }
}
