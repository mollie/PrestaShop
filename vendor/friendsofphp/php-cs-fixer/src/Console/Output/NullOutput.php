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
namespace MolliePrefix\PhpCsFixer\Console\Output;

/**
 * @internal
 */
final class NullOutput implements \MolliePrefix\PhpCsFixer\Console\Output\ProcessOutputInterface
{
    public function printLegend()
    {
    }
}
