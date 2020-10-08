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
namespace MolliePrefix\PhpCsFixer\Tests\Test;

use MolliePrefix\Symfony\Component\Finder\SplFileInfo;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class InternalIntegrationCaseFactory extends \MolliePrefix\PhpCsFixer\Tests\Test\AbstractIntegrationCaseFactory
{
    /**
     * {@inheritdoc}
     */
    protected function determineSettings(\MolliePrefix\Symfony\Component\Finder\SplFileInfo $file, $config)
    {
        $parsed = parent::determineSettings($file, $config);
        $parsed['isExplicitPriorityCheck'] = \in_array('priority', \explode(\DIRECTORY_SEPARATOR, $file->getRelativePathname()), \true);
        return $parsed;
    }
}
