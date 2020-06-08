<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\BooleanNode;
class BooleanNodeTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testNormalize($value)
    {
        $node = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\BooleanNode('test');
        $this->assertSame($value, $node->normalize($value));
    }
    /**
     * @dataProvider getValidValues
     *
     * @param bool $value
     */
    public function testValidNonEmptyValues($value)
    {
        $node = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\BooleanNode('test');
        $node->setAllowEmptyValue(\false);
        $this->assertSame($value, $node->finalize($value));
    }
    public function getValidValues()
    {
        return [[\false], [\true]];
    }
    /**
     * @dataProvider getInvalidValues
     */
    public function testNormalizeThrowsExceptionOnInvalidValues($value)
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidTypeException');
        $node = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\BooleanNode('test');
        $node->normalize($value);
    }
    public function getInvalidValues()
    {
        return [[null], [''], ['foo'], [0], [1], [0.0], [0.1], [[]], [['foo' => 'bar']], [new \stdClass()]];
    }
}
