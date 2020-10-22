<?php

/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\SebastianBergmann\Comparator;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\Factory
 *
 */
class FactoryTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function instanceProvider()
    {
        $tmpfile = \tmpfile();
        return array(
            array(\NULL, \NULL, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\NULL, \TRUE, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\TRUE, \NULL, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\TRUE, \TRUE, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\FALSE, \FALSE, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\TRUE, \FALSE, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(\FALSE, \TRUE, 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array('', '', 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array('0', '0', 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array('0', 0, 'MolliePrefix\\SebastianBergmann\\Comparator\\NumericComparator'),
            array(0, '0', 'MolliePrefix\\SebastianBergmann\\Comparator\\NumericComparator'),
            array(0, 0, 'MolliePrefix\\SebastianBergmann\\Comparator\\NumericComparator'),
            array(1.0, 0, 'MolliePrefix\\SebastianBergmann\\Comparator\\DoubleComparator'),
            array(0, 1.0, 'MolliePrefix\\SebastianBergmann\\Comparator\\DoubleComparator'),
            array(1.0, 1.0, 'MolliePrefix\\SebastianBergmann\\Comparator\\DoubleComparator'),
            array(array(1), array(1), 'MolliePrefix\\SebastianBergmann\\Comparator\\ArrayComparator'),
            array($tmpfile, $tmpfile, 'MolliePrefix\\SebastianBergmann\\Comparator\\ResourceComparator'),
            array(new \stdClass(), new \stdClass(), 'MolliePrefix\\SebastianBergmann\\Comparator\\ObjectComparator'),
            array(new \DateTime(), new \DateTime(), 'MolliePrefix\\SebastianBergmann\\Comparator\\DateTimeComparator'),
            array(new \SplObjectStorage(), new \SplObjectStorage(), 'MolliePrefix\\SebastianBergmann\\Comparator\\SplObjectStorageComparator'),
            array(new \Exception(), new \Exception(), 'MolliePrefix\\SebastianBergmann\\Comparator\\ExceptionComparator'),
            array(new \DOMDocument(), new \DOMDocument(), 'MolliePrefix\\SebastianBergmann\\Comparator\\DOMNodeComparator'),
            // mixed types
            array($tmpfile, array(1), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), $tmpfile, 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array($tmpfile, '1', 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array('1', $tmpfile, 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array($tmpfile, new \stdClass(), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass(), $tmpfile, 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass(), array(1), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), new \stdClass(), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass(), '1', 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array('1', new \stdClass(), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), '1', 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array('1', new \MolliePrefix\SebastianBergmann\Comparator\ClassWithToString(), 'MolliePrefix\\SebastianBergmann\\Comparator\\ScalarComparator'),
            array(1.0, new \stdClass(), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass(), 1.0, 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(1.0, array(1), 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), 1.0, 'MolliePrefix\\SebastianBergmann\\Comparator\\TypeComparator'),
        );
    }
    /**
     * @dataProvider instanceProvider
     * @covers       ::getComparatorFor
     * @covers       ::__construct
     */
    public function testGetComparatorFor($a, $b, $expected)
    {
        $factory = new \MolliePrefix\SebastianBergmann\Comparator\Factory();
        $actual = $factory->getComparatorFor($a, $b);
        $this->assertInstanceOf($expected, $actual);
    }
    /**
     * @covers ::register
     */
    public function testRegister()
    {
        $comparator = new \MolliePrefix\SebastianBergmann\Comparator\TestClassComparator();
        $factory = new \MolliePrefix\SebastianBergmann\Comparator\Factory();
        $factory->register($comparator);
        $a = new \MolliePrefix\SebastianBergmann\Comparator\TestClass();
        $b = new \MolliePrefix\SebastianBergmann\Comparator\TestClass();
        $expected = 'MolliePrefix\\SebastianBergmann\\Comparator\\TestClassComparator';
        $actual = $factory->getComparatorFor($a, $b);
        $factory->unregister($comparator);
        $this->assertInstanceOf($expected, $actual);
    }
    /**
     * @covers ::unregister
     */
    public function testUnregister()
    {
        $comparator = new \MolliePrefix\SebastianBergmann\Comparator\TestClassComparator();
        $factory = new \MolliePrefix\SebastianBergmann\Comparator\Factory();
        $factory->register($comparator);
        $factory->unregister($comparator);
        $a = new \MolliePrefix\SebastianBergmann\Comparator\TestClass();
        $b = new \MolliePrefix\SebastianBergmann\Comparator\TestClass();
        $expected = 'MolliePrefix\\SebastianBergmann\\Comparator\\ObjectComparator';
        $actual = $factory->getComparatorFor($a, $b);
        $this->assertInstanceOf($expected, $actual);
    }
}
