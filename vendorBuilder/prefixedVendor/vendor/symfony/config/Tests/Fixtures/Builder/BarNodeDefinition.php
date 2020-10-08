<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Fixtures\Builder;

use MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeDefinition;
use MolliePrefix\Symfony\Component\Config\Tests\Fixtures\BarNode;
class BarNodeDefinition extends \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeDefinition
{
    protected function createNode()
    {
        return new \MolliePrefix\Symfony\Component\Config\Tests\Fixtures\BarNode($this->name);
    }
}
