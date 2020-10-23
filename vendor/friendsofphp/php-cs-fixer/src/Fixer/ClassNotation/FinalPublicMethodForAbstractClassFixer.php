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
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class FinalPublicMethodForAbstractClassFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer
{
    /**
     * @var array
     */
    private $magicMethods = ['__construct' => \true, '__destruct' => \true, '__call' => \true, '__callstatic' => \true, '__get' => \true, '__set' => \true, '__isset' => \true, '__unset' => \true, '__sleep' => \true, '__wakeup' => \true, '__tostring' => \true, '__invoke' => \true, '__set_state' => \true, '__clone' => \true, '__debuginfo' => \true];
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('All `public` methods of `abstract` classes should be `final`.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php

abstract class AbstractMachine
{
    public function start()
    {}
}
')], 'Enforce API encapsulation in an inheritance architecture. ' . 'If you want to override a method, use the Template method pattern.', 'Risky when overriding `public` methods of `abstract` classes.');
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_ABSTRACT, \T_PUBLIC, \T_FUNCTION]);
    }
    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $classes = \array_keys($tokens->findGivenKind(\T_CLASS));
        while ($classIndex = \array_pop($classes)) {
            $prevToken = $tokens[$tokens->getPrevMeaningfulToken($classIndex)];
            if (!$prevToken->isGivenKind([\T_ABSTRACT])) {
                continue;
            }
            $classOpen = $tokens->getNextTokenOfKind($classIndex, ['{']);
            $classClose = $tokens->findBlockEnd(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpen);
            $this->fixClass($tokens, $classOpen, $classClose);
        }
    }
    /**
     * @param int $classOpenIndex
     * @param int $classCloseIndex
     */
    private function fixClass(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $classOpenIndex, $classCloseIndex)
    {
        for ($index = $classCloseIndex - 1; $index > $classOpenIndex; --$index) {
            // skip method contents
            if ($tokens[$index]->equals('}')) {
                $index = $tokens->findBlockStart(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }
            // skip non public methods
            if (!$tokens[$index]->isGivenKind(\T_PUBLIC)) {
                continue;
            }
            $nextIndex = $tokens->getNextMeaningfulToken($index);
            $nextToken = $tokens[$nextIndex];
            if ($nextToken->isGivenKind(\T_STATIC)) {
                $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
                $nextToken = $tokens[$nextIndex];
            }
            // skip uses, attributes, constants etc
            if (!$nextToken->isGivenKind(\T_FUNCTION)) {
                continue;
            }
            $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            $nextToken = $tokens[$nextIndex];
            // skip magic methods
            if (isset($this->magicMethods[\strtolower($nextToken->getContent())])) {
                continue;
            }
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$prevIndex];
            if ($prevToken->isGivenKind(\T_STATIC)) {
                $index = $prevIndex;
                $prevIndex = $tokens->getPrevMeaningfulToken($index);
                $prevToken = $tokens[$prevIndex];
            }
            // skip abstract or already final methods
            if ($prevToken->isGivenKind([\T_ABSTRACT, \T_FINAL])) {
                $index = $prevIndex;
                continue;
            }
            $tokens->insertAt($index, [new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_FINAL, 'final']), new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
        }
    }
}
