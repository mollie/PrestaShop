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
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
final class SelfStaticAccessorFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * @var TokensAnalyzer
     */
    private $tokensAnalyzer;
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Inside a `final` class or anonymous class `self` should be preferred to `static`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Sample
{
    private static $A = 1;

    public function getBar()
    {
        return static::class.static::test().static::$A;
    }

    private static function test()
    {
        return \'test\';
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Foo
{
    public function bar()
    {
        return new static();
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Foo
{
    public function isBar()
    {
        return $foo instanceof static;
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php
$a = new class() {
    public function getBar()
    {
        return static::class;
    }
};
', new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70000))]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_STATIC]) && $tokens->isAnyTokenKindsFound([\T_DOUBLE_COLON, \T_NEW, \T_INSTANCEOF]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after FinalInternalClassFixer, FunctionToConstantFixer, PhpUnitTestCaseStaticMethodCallsFixer.
     */
    public function getPriority()
    {
        return -10;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $this->tokensAnalyzer = $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $classIndex = $tokens->getNextTokenOfKind(0, [[\T_CLASS]]);
        while (null !== $classIndex) {
            if ($tokens[$tokens->getPrevMeaningfulToken($classIndex)]->isGivenKind(\T_FINAL) || $tokensAnalyzer->isAnonymousClass($classIndex)) {
                $classIndex = $this->fixClass($tokens, $classIndex);
            }
            $classIndex = $tokens->getNextTokenOfKind($classIndex, [[\T_CLASS]]);
        }
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function fixClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $index = $tokens->getNextTokenOfKind($index, ['{']);
        $classOpenCount = 1;
        while ($classOpenCount > 0) {
            ++$index;
            if ($tokens[$index]->equals('{')) {
                ++$classOpenCount;
                continue;
            }
            if ($tokens[$index]->equals('}')) {
                --$classOpenCount;
                continue;
            }
            if ($tokens[$index]->isGivenKind(\T_FUNCTION)) {
                // do not fix inside lambda
                if ($this->tokensAnalyzer->isLambda($index)) {
                    // figure out where the lambda starts
                    $index = $tokens->getNextTokenOfKind($index, ['{']);
                    $openCount = 1;
                    do {
                        $index = $tokens->getNextTokenOfKind($index, ['}', '{', [\T_CLASS]]);
                        if ($tokens[$index]->equals('}')) {
                            --$openCount;
                        } elseif ($tokens[$index]->equals('{')) {
                            ++$openCount;
                        } else {
                            $index = $this->fixClass($tokens, $index);
                        }
                    } while ($openCount > 0);
                }
                continue;
            }
            if ($tokens[$index]->isGivenKind([\T_NEW, \T_INSTANCEOF])) {
                $index = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$index]->isGivenKind(\T_STATIC)) {
                    $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'self']);
                }
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_STATIC)) {
                continue;
            }
            $staticIndex = $index;
            $index = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$index]->isGivenKind(\T_DOUBLE_COLON)) {
                continue;
            }
            $tokens[$staticIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, 'self']);
        }
        return $index;
    }
}
