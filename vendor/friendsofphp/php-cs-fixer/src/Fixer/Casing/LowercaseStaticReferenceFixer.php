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
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class LowercaseStaticReferenceFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Class static references `self`, `static` and `parent` MUST be in lower case.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class Foo extends Bar
{
    public function baz1()
    {
        return STATIC::baz2();
    }

    public function baz2($x)
    {
        return $x instanceof Self;
    }

    public function baz3(PaRent $x)
    {
        return true;
    }
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php
class Foo extends Bar
{
    public function baz(?self $x) : SELF
    {
        return false;
    }
}
', new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(70100))]);
    }
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([\T_STATIC, \T_STRING]);
    }
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny([[\T_STRING, 'self'], [\T_STATIC, 'static'], [\T_STRING, 'parent']], \false)) {
                continue;
            }
            $newContent = \strtolower($token->getContent());
            if ($token->getContent() === $newContent) {
                continue;
                // case is already correct
            }
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevIndex]->isGivenKind([\T_CONST, \T_DOUBLE_COLON, \T_FUNCTION, \T_NAMESPACE, \T_NS_SEPARATOR, \T_OBJECT_OPERATOR, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC])) {
                continue;
            }
            $nextIndex = $tokens->getNextMeaningfulToken($index);
            if ($tokens[$nextIndex]->isGivenKind([\T_FUNCTION, \T_NS_SEPARATOR, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC])) {
                continue;
            }
            if ('static' === $newContent && $tokens[$nextIndex]->isGivenKind(\T_VARIABLE)) {
                continue;
            }
            $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([$token->getId(), $newContent]);
        }
    }
}
