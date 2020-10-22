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
namespace MolliePrefix\PhpCsFixer\Fixer\PhpUnit;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PhpUnitFqcnAnnotationFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('PHPUnit annotations should be a FQCNs including a root namespace.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
final class MyTest extends \\PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @covers Project\\NameSpace\\Something
     * @coversDefaultClass Project\\Default
     * @uses Project\\Test\\Util
     */
    public function testSomeTest()
    {
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoUnusedImportsFixer, PhpUnitOrderedCoversFixer.
     */
    public function getPriority()
    {
        return -9;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_DOC_COMMENT]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $phpUnitTestCaseIndicator = new \MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator();
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indexes) {
            $startIndex = $indexes[0];
            $prevDocCommentIndex = $tokens->getPrevTokenOfKind($startIndex, [[\T_DOC_COMMENT]]);
            if (null !== $prevDocCommentIndex) {
                $startIndex = $prevDocCommentIndex;
            }
            $this->fixPhpUnitClass($tokens, $startIndex, $indexes[1]);
        }
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function fixPhpUnitClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        for ($index = $startIndex; $index < $endIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, \MolliePrefix\PhpCsFixer\Preg::replace('~^(\\s*\\*\\s*@(?:expectedException|covers|coversDefaultClass|uses)\\h+)(?!(?:self|static)::)(\\w.*)$~m', '$1\\\\$2', $tokens[$index]->getContent())]);
            }
        }
    }
}
