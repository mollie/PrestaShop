<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Definition;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Definition\BooleanNode;
class BooleanNodeTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testNormalize($value)
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\BooleanNode('test');
        $this->assertSame($value, $node->normalize($value));
    }
    /**
     * @dataProvider getValidValues
     *
     * @param bool $value
     */
    public function testValidNonEmptyValues($value)
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\BooleanNode('test');
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
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidTypeException');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\BooleanNode('test');
        $node->normalize($value);
    }
    public function getInvalidValues()
    {
        return [[null], [''], ['foo'], [0], [1], [0.0], [0.1], [[]], [['foo' => 'bar']], [new \stdClass()]];
    }
}
