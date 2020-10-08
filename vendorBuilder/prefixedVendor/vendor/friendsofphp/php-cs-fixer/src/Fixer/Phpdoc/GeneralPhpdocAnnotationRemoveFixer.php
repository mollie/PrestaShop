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
namespace MolliePrefix\PhpCsFixer\Fixer\Phpdoc;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class GeneralPhpdocAnnotationRemoveFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Configured annotations should be omitted from PHPDoc.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @internal
 * @author John Doe
 */
function foo() {}
', ['annotations' => ['author']]), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @author John Doe
 * @package ACME API
 * @subpackage Authorization
 * @version 1.0
 */
function foo() {}
', ['annotations' => ['package', 'subpackage']])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoEmptyPhpdocFixer, PhpdocAlignFixer, PhpdocLineSpanFixer, PhpdocSeparationFixer, PhpdocTrimFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 10;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        if (!\count($this->configuration['annotations'])) {
            return;
        }
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType($this->configuration['annotations']);
            // nothing to do if there are no annotations
            if (empty($annotations)) {
                continue;
            }
            foreach ($annotations as $annotation) {
                $annotation->remove();
            }
            if ('' === $doc->getContent()) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            } else {
                $tokens[$index] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $doc->getContent()]);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolverRootless('annotations', [(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('annotations', 'List of annotations to remove, e.g. `["author"]`.'))->setAllowedTypes(['array'])->setDefault([])->getOption()], $this->getName());
    }
}
