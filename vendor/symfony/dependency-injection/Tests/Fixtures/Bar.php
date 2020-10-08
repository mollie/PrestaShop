<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures;

class Bar implements \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\BarInterface
{
    public $quz;
    public function __construct($quz = null, \MolliePrefix\NonExistent $nonExistent = null, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\BarInterface $decorated = null, array $foo = [])
    {
        $this->quz = $quz;
    }
    public static function create(\MolliePrefix\NonExistent $nonExistent = null, $factory = null)
    {
    }
}
