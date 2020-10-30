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
namespace MolliePrefix\PhpCsFixer\Fixer\FunctionNotation;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class FunctionTypehintSpaceFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Ensure single space between function\'s argument and its typehint.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nfunction sample(array\$a)\n{}\n"), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nfunction sample(array  \$a)\n{}\n")]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (\PHP_VERSION_ID >= 70400 && $tokens->isTokenKindFound(\T_FN)) {
            return \true;
        }
        return $tokens->isTokenKindFound(\T_FUNCTION);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $functionsAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(\T_FUNCTION) && (\PHP_VERSION_ID < 70400 || !$token->isGivenKind(\T_FN))) {
                continue;
            }
            $arguments = $functionsAnalyzer->getFunctionArguments($tokens, $index);
            foreach (\array_reverse($arguments) as $argument) {
                $type = $argument->getTypeAnalysis();
                if (!$type instanceof \MolliePrefix\PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis) {
                    continue;
                }
                $whitespaceTokenIndex = $type->getEndIndex() + 1;
                if ($tokens[$whitespaceTokenIndex]->equals([\T_WHITESPACE])) {
                    if (' ' === $tokens[$whitespaceTokenIndex]->getContent()) {
                        continue;
                    }
                    $tokens->clearAt($whitespaceTokenIndex);
                }
                $tokens->insertAt($whitespaceTokenIndex, new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' ']));
            }
        }
    }
}
