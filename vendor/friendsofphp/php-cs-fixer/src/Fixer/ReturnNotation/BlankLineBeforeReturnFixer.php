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
namespace MolliePrefix\PhpCsFixer\Fixer\ReturnNotation;

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * @deprecated since 2.4, replaced by BlankLineBeforeStatementFixer
 *
 * @todo To be removed at 3.0
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Andreas Möller <am@localheinz.com>
 */
final class BlankLineBeforeReturnFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface, \MolliePrefix\PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('An empty line feed should precede a return statement.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\nfunction A()\n{\n    echo 1;\n    return 1;\n}\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoUselessReturnFixer.
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
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
    protected function createProxyFixers()
    {
        $fixer = new \MolliePrefix\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer();
        $fixer->configure(['statements' => ['return']]);
        return [$fixer];
    }
}
