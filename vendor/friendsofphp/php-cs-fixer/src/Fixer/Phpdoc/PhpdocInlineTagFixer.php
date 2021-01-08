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

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * Fix inline tags and make inheritdoc tag always inline.
 *
 * @deprecated since 2.9, replaced by PhpdocInlineTagNormalizerFixer GeneralPhpdocTagRenameFixer
 *
 * @TODO To be removed at 3.0
 */
final class PhpdocInlineTagFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSuccessorsNames()
    {
        return \array_keys($this->proxyFixers);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Fix PHPDoc inline tags, make `@inheritdoc` always inline.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
/**
 * @{TUTORIAL}
 * {{ @link }}
 * {@examples}
 * @inheritdocs
 */
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    protected function createProxyFixers()
    {
        $inlineNormalizerFixer = new \MolliePrefix\PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer();
        $renameFixer = new \MolliePrefix\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocTagRenameFixer();
        $renameFixer->configure(['fix_annotation' => \true, 'fix_inline' => \true, 'replacements' => ['inheritdoc' => 'inheritdoc', 'inheritdocs' => 'inheritdoc'], 'case_sensitive' => \false]);
        $tagTypeFixer = new \MolliePrefix\PhpCsFixer\Fixer\Phpdoc\PhpdocTagTypeFixer();
        $tagTypeFixer->configure(['tags' => ['inheritdoc' => 'inline']]);
        return [$inlineNormalizerFixer, $renameFixer, $tagTypeFixer];
    }
}
