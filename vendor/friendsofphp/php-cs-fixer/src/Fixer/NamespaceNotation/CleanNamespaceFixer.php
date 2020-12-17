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
namespace MolliePrefix\PhpCsFixer\Fixer\NamespaceNotation;

use MolliePrefix\PhpCsFixer\AbstractLinesBeforeNamespaceFixer;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification;
use MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
final class CleanNamespaceFixer extends \MolliePrefix\PhpCsFixer\AbstractLinesBeforeNamespaceFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $samples = [];
        foreach (['namespace Foo \\ Bar;', 'echo foo /* comment */ \\ bar();'] as $sample) {
            $samples[] = new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecificCodeSample("<?php\n" . $sample . "\n", new \MolliePrefix\PhpCsFixer\FixerDefinition\VersionSpecification(null, 80000 - 1));
        }
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Namespace must not contain spacing, comments or PHPDoc.', $samples);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return \PHP_VERSION_ID < 80000 && $tokens->isTokenKindFound(\T_NS_SEPARATOR);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $count = $tokens->count();
        for ($index = 0; $index < $count; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                $previousIndex = $tokens->getPrevMeaningfulToken($index);
                $index = $this->fixNamespace($tokens, $tokens[$previousIndex]->isGivenKind(\T_STRING) ? $previousIndex : $index);
            }
        }
    }
    /**
     * @param int $index start of namespace
     *
     * @return int
     */
    private function fixNamespace(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $spaceIndexes = [];
        while ($tokens[++$index]->isGivenKind([\T_NS_SEPARATOR, \T_STRING, \T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
            if ($tokens[$index]->isGivenKind(\T_WHITESPACE)) {
                $spaceIndexes[] = $index;
            } elseif ($tokens[$index]->isComment()) {
                $tokens->clearAt($index);
            }
        }
        if ($tokens[$index - 1]->isWhiteSpace()) {
            \array_pop($spaceIndexes);
        }
        foreach ($spaceIndexes as $i) {
            $tokens->clearAt($i);
        }
        return $index;
    }
}
