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
namespace MolliePrefix\PhpCsFixer\Fixer\Casing;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\CT;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class NativeFunctionCasingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Function defined by PHP should be called using the correct casing.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nSTRLEN(\$str);\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after FunctionToConstantFixer, NoUselessSprintfFixer, PowToExponentiationFixer.
     */
    public function getPriority()
    {
        return 0;
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
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        static $nativeFunctionNames = null;
        if (null === $nativeFunctionNames) {
            $nativeFunctionNames = $this->getNativeFunctionNames();
        }
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            // test if we are at a function all
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }
            $next = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$next]->equals('(')) {
                $index = $next;
                continue;
            }
            $functionNamePrefix = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$functionNamePrefix]->isGivenKind([\T_DOUBLE_COLON, \T_NEW, \T_OBJECT_OPERATOR, \T_FUNCTION, \MolliePrefix\PhpCsFixer\Tokenizer\CT::T_RETURN_REF])) {
                continue;
            }
            if ($tokens[$functionNamePrefix]->isGivenKind(\T_NS_SEPARATOR)) {
                // skip if the call is to a constructor or to a function in a namespace other than the default
                $prev = $tokens->getPrevMeaningfulToken($functionNamePrefix);
                if ($tokens[$prev]->isGivenKind([\T_STRING, \T_NEW])) {
                    continue;
                }
            }
            // test if the function call is to a native PHP function
            $lower = \strtolower($tokens[$index]->getContent());
            if (!\array_key_exists($lower, $nativeFunctionNames)) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $nativeFunctionNames[$lower]]);
            $index = $next;
        }
    }
    /**
     * @return array<string, string>
     */
    private function getNativeFunctionNames()
    {
        $allFunctions = \get_defined_functions();
        $functions = [];
        foreach ($allFunctions['internal'] as $function) {
            $functions[\strtolower($function)] = $function;
        }
        return $functions;
    }
}
