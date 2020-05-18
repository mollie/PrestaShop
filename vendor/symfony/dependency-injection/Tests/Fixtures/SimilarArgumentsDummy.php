<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures;

class SimilarArgumentsDummy
{
    public $class1;
    public $class2;
    public function __construct(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass $class1, $token, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass $class2)
    {
        $this->class1 = $class1;
        $this->class2 = $class2;
    }
}
