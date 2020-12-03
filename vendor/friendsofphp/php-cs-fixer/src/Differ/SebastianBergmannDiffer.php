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
namespace MolliePrefix\PhpCsFixer\Differ;

use MolliePrefix\PhpCsFixer\Diff\v1_4\Differ;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class SebastianBergmannDiffer implements \MolliePrefix\PhpCsFixer\Differ\DifferInterface
{
    /**
     * @var Differ
     */
    private $differ;
    public function __construct()
    {
        $this->differ = new \MolliePrefix\PhpCsFixer\Diff\v1_4\Differ();
    }
    /**
     * {@inheritdoc}
     */
    public function diff($old, $new)
    {
        return $this->differ->diff($old, $new);
    }
}
