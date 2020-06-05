<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\ParameterBag;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
class FrozenParameterBagTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $parameters = ['foo' => 'foo', 'bar' => 'bar'];
        $bag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag($parameters);
        $this->assertEquals($parameters, $bag->all(), '__construct() takes an array of parameters as its first argument');
    }
    public function testClear()
    {
        $this->expectException('LogicException');
        $bag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag([]);
        $bag->clear();
    }
    public function testSet()
    {
        $this->expectException('LogicException');
        $bag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag([]);
        $bag->set('foo', 'bar');
    }
    public function testAdd()
    {
        $this->expectException('LogicException');
        $bag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag([]);
        $bag->add([]);
    }
    public function testRemove()
    {
        $this->expectException('LogicException');
        $bag = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag(['foo' => 'bar']);
        $bag->remove('foo');
    }
}
