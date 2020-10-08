<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PHPUnit_Framework_TestCase;
use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Comparison;
class ComparisonTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @var DecimalNumber
     */
    private static $zero;
    public static function setUpBeforeClass()
    {
        static::$zero = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0');
    }
    /**
     * Given two numbers
     * When comparing them
     * Then we should get
     * - 1 if a > b
     * - -1 if b > a
     * - 0 if a == b
     *
     * @param string $a
     * @param string $b
     * @param int $expected
     *
     * @dataProvider provideCompareTestCases
     */
    public function testItComparesNumbers($a, $b, $expected)
    {
        $comparison = new \MolliePrefix\PrestaShop\Decimal\Operation\Comparison();
        $result1 = $comparison->compareUsingBcMath(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($a), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($b));
        $result2 = $comparison->compareWithoutBcMath(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($a), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($b));
        $this->assertSame($expected, $result1, "Failed assertion (BC Math)");
        $this->assertSame($expected, $result2, "Failed assertion");
    }
    public function provideCompareTestCases()
    {
        return [
            // a is greater
            'greater 1' => ['1', '0', 1],
            'greater 2' => ['1.0', '0', 1],
            'greater 3' => ['1.01', '1.0', 1],
            'greater 4' => ['1.0000000000000000000000001', '1.0', 1],
            'greater 5' => ['10', '001', 1],
            'greater 6' => ['10', '-10', 1],
            'greater 7' => ['10', '-100', 1],
            'greater 8' => ['100', '10', 1],
            'greater 9' => ['-1', '-2', 1],
            'greater 10' => ['-1', '-0000002', 1],
            'greater 11' => ['-1', '-1.0000000001', 1],
            // a is equal
            'equal 1' => ['1', '01', 0],
            'equal 2' => ['0.1', '0000.1000000000000', 0],
            // a is lower
            'lower 1' => ['0', '1', -1],
            'lower 2' => ['-1', '0', -1],
            'lower 3' => ['-1', '0.0001', -1],
            'lower 4' => ['-2', '-1', -1],
            'lower 5' => ['-02', '-1', -1],
            'lower 6' => ['-2', '-01', -1],
            'lower 8' => ['10', '100', -1],
            'lower 9' => ['-1.000001', '-1', -1],
            'lower 10' => ['-1000.000001', '-10.0001', -1],
        ];
    }
    /**
     * Given a number
     * It should detect if it equals zero
     *
     * @param string $number
     * @param bool $expected
     *
     * @dataProvider provideEqualsZeroTests
     */
    public function testItDetectsEqualsZero($number, $expected)
    {
        $n = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, $n->equalsZero(), \sprintf("Failed to assert that %s %s equal to zero", $number, $this->getIsVerb($expected)));
        // double check
        $this->assertSame($expected, $n->equals(static::$zero), \sprintf("Failed to assert that %s %s equal to Number zero", $number, $this->getIsVerb($expected)));
    }
    public function provideEqualsZeroTests()
    {
        return [['0', \true], ['000000', \true], ['0.0000', \true], ['-0.0000', \true], ['0.0001', \false], ['-0.0001', \false], ['10', \false], ['10.0', \false], ['10.000001', \false], ['10.100001', \false], ['-10', \false], ['-10.0', \false], ['-10.000001', \false], ['-10.100001', \false]];
    }
    /**
     * Given a number
     * It should detect if it's greater than zero
     *
     * @param string $number
     * @param bool $expected
     *
     * @dataProvider provideGreaterThanZeroTests
     */
    public function testItDetectsGreaterThanZero($number, $expected)
    {
        $n = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, $n->isGreaterThanZero(), \sprintf("Failed to assert that %s %s greater than zero", $number, $this->getIsVerb($expected)));
        // double check
        $this->assertSame($expected, $n->isGreaterThan(static::$zero), \sprintf("Failed to assert that %s %s grater to Number zero", $number, $this->getIsVerb($expected)));
    }
    public function provideGreaterThanZeroTests()
    {
        return [['0', \false], ['000000', \false], ['0.0000', \false], ['-0.0000', \false], ['0.0001', \true], ['-0.0001', \false], ['10', \true], ['10.0', \true], ['10.000001', \true], ['10.100001', \true], ['-10', \false], ['-10.0', \false], ['-10.000001', \false], ['-10.100001', \false]];
    }
    /**
     * Given a number
     * It should detect if it's greater than zero
     *
     * @param string $number
     * @param bool $expected
     *
     * @dataProvider provideGreaterOrEqualThanZeroTests
     */
    public function testItDetectsGreaterOrEqualThanZero($number, $expected)
    {
        $n = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, $n->isGreaterOrEqualThanZero(), \sprintf("Failed to assert that %s %s greater or equal than zero", $number, $this->getIsVerb($expected)));
        // double check
        $this->assertSame($expected, $n->isGreaterOrEqualThan(static::$zero), \sprintf("Failed to assert that %s %s greater or equal to Number zero", $number, $this->getIsVerb($expected)));
    }
    public function provideGreaterOrEqualThanZeroTests()
    {
        return [['0', \true], ['000000', \true], ['0.0000', \true], ['-0.0000', \true], ['0.0001', \true], ['-0.0001', \false], ['10', \true], ['10.0', \true], ['10.000001', \true], ['10.100001', \true], ['-10', \false], ['-10.0', \false], ['-10.000001', \false], ['-10.100001', \false]];
    }
    /**
     * Given a number
     * It should detect if it's lower than zero
     *
     * @param string $number
     * @param bool $expected
     *
     * @dataProvider provideLowerThanZeroTests
     */
    public function testItDetectsLowerThanZero($number, $expected)
    {
        $n = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, $n->isLowerThanZero(), \sprintf("Failed to assert that %s %s lower than zero", $number, $this->getIsVerb($expected)));
        // double check
        $this->assertSame($expected, $n->isLowerThan(static::$zero), \sprintf("Failed to assert that %s %s lower to Number zero", $number, $this->getIsVerb($expected)));
    }
    public function provideLowerThanZeroTests()
    {
        return [['0', \false], ['000000', \false], ['0.0000', \false], ['-0.0000', \false], ['0.0001', \false], ['-0.0001', \true], ['10', \false], ['10.0', \false], ['10.000001', \false], ['10.100001', \false], ['-10', \true], ['-10.0', \true], ['-10.000001', \true], ['-10.100001', \true]];
    }
    /**
     * Given a number
     * It should detect if it's lower than zero
     *
     * @param string $number
     * @param bool $expected
     *
     * @dataProvider provideLowerOrEqualThanZeroTests
     */
    public function testItDetectsLowerOrEqualThanZero($number, $expected)
    {
        $n = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, $n->isLowerOrEqualThanZero(), \sprintf("Failed to assert that %s %s lower or equal than zero", $number, $this->getIsVerb($expected)));
        // double check
        $this->assertSame($expected, $n->isLowerOrEqualThan(static::$zero), \sprintf("Failed to assert that %s %s lower or equal to Number zero", $number, $this->getIsVerb($expected)));
    }
    public function provideLowerOrEqualThanZeroTests()
    {
        return [['0', \true], ['000000', \true], ['0.0000', \true], ['-0.0000', \true], ['0.0001', \false], ['-0.0001', \true], ['10', \false], ['10.0', \false], ['10.000001', \false], ['10.100001', \false], ['-10', \true], ['-10.0', \true], ['-10.000001', \true], ['-10.100001', \true]];
    }
    /**
     * @param bool $assertion
     *
     * @return string
     */
    private function getIsVerb($assertion)
    {
        return $assertion ? 'is' : 'is NOT';
    }
}
