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

use stdClass;

class FactoryDummy extends FactoryParent
{
    public static function createFactory() : FactoryDummy
    {
    }
    public function create() : stdClass
    {
    }
    // Not supported by hhvm
    public function createBuiltin() : int
    {
    }
    public static function createSelf() : self
    {
    }
    public static function createParent() : \_PhpScoper5ea00cc67502b\parent
    {
    }
}
class FactoryParent
{
}
function factoryFunction() : FactoryDummy
{
}
