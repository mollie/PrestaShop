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
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author ntzm
 */
final class NoNullPropertyInitializationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Properties MUST not be explicitly initialized with `null` except when they have a type declaration (PHP 7.4).', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class Foo {
    public $foo = null;
}
')]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_TRAIT]) && $tokens->isAnyTokenKindsFound([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_VAR]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_VAR])) {
                continue;
            }
            while (\true) {
                $varTokenIndex = $index = $tokens->getNextMeaningfulToken($index);
                if (!$tokens[$index]->isGivenKind(\T_VARIABLE)) {
                    break;
                }
                $index = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$index]->equals('=')) {
                    $index = $tokens->getNextMeaningfulToken($index);
                    if ($tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                        $index = $tokens->getNextMeaningfulToken($index);
                    }
                    if ($tokens[$index]->equals([\T_STRING, 'null'], \false)) {
                        for ($i = $varTokenIndex + 1; $i <= $index; ++$i) {
                            if (!($tokens[$i]->isWhitespace() && \false !== \strpos($tokens[$i]->getContent(), "\n")) && !$tokens[$i]->isComment()) {
                                $tokens->clearAt($i);
                            }
                        }
                    }
                    ++$index;
                }
                if (!$tokens[$index]->equals(',')) {
                    break;
                }
            }
        }
    }
}
