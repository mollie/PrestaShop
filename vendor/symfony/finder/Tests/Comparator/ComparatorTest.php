<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Finder\Tests\Comparator;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Finder\Comparator\Comparator;
class ComparatorTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetSetOperator()
    {
        $comparator = new \MolliePrefix\Symfony\Component\Finder\Comparator\Comparator();
        try {
            $comparator->setOperator('foo');
            $this->fail('->setOperator() throws an \\InvalidArgumentException if the operator is not valid.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->setOperator() throws an \\InvalidArgumentException if the operator is not valid.');
        }
        $comparator = new \MolliePrefix\Symfony\Component\Finder\Comparator\Comparator();
        $comparator->setOperator('>');
        $this->assertEquals('>', $comparator->getOperator(), '->getOperator() returns the current operator');
    }
    public function testGetSetTarget()
    {
        $comparator = new \MolliePrefix\Symfony\Component\Finder\Comparator\Comparator();
        $comparator->setTarget(8);
        $this->assertEquals(8, $comparator->getTarget(), '->getTarget() returns the target');
    }
    /**
     * @dataProvider getTestData
     */
    public function testTest($operator, $target, $match, $noMatch)
    {
        $c = new \MolliePrefix\Symfony\Component\Finder\Comparator\Comparator();
        $c->setOperator($operator);
        $c->setTarget($target);
        foreach ($match as $m) {
            $this->assertTrue($c->test($m), '->test() tests a string against the expression');
        }
        foreach ($noMatch as $m) {
            $this->assertFalse($c->test($m), '->test() tests a string against the expression');
        }
    }
    public function getTestData()
    {
        return [['<', '1000', ['500', '999'], ['1000', '1500']]];
    }
}
