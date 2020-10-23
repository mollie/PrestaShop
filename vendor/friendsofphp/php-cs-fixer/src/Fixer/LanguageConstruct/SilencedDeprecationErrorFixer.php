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
namespace MolliePrefix\PhpCsFixer\Fixer\LanguageConstruct;

use MolliePrefix\PhpCsFixer\AbstractProxyFixer;
use MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
/**
 * @author Jules Pietri <jules@heahprod.com>
 *
 * @deprecated
 */
final class SilencedDeprecationErrorFixer extends \MolliePrefix\PhpCsFixer\AbstractProxyFixer implements \MolliePrefix\PhpCsFixer\Fixer\DeprecatedFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Ensures deprecation notices are silenced.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample("<?php\ntrigger_error('Warning.', E_USER_DEPRECATED);\n")], null, 'Silencing of deprecation errors might cause changes to code behaviour.');
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
        return [new \MolliePrefix\PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer()];
    }
}
