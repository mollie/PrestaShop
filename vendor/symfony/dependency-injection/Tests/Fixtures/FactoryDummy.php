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

class FactoryDummy extends \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryParent
{
    public static function createFactory() : \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy
    {
    }
    public function create() : \stdClass
    {
    }
    // Not supported by hhvm
    public function createBuiltin() : int
    {
    }
    public static function createSelf() : self
    {
    }
    public static function createParent() : \MolliePrefix\parent
    {
    }
}
class FactoryParent
{
}
function factoryFunction() : \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy
{
}
