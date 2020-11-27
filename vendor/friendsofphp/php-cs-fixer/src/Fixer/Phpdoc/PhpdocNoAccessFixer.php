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
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpdocNoAccessFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('`@access` annotations should be omitted from PHPDoc.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class Foo
{
    /**
     * @internal
     * @access private
     */
    private $bar;
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoEmptyPhpdocFixer, PhpdocAlignFixer, PhpdocOrderFixer, PhpdocSeparationFixer, PhpdocTrimFixer.
     * Must run after CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
    /**
     * {@inheritdoc}
     */
    protected function createProxyFixers()
    {
        $fixer = new \MolliePrefix\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer();
        $fixer->configure(['annotations' => ['access']]);
        return [$fixer];
    }
}
