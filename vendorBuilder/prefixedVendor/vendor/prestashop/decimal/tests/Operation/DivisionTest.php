<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Division;
class DivisionTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given two decimal numbers
     * When computing the division operation between them
     * Then we should get the result of dividing number1 by number2
     *
     * @param string $number1
     * @param string $number2
     * @param string $expectedResult
     *
     * @dataProvider provideNumbersToDivide
     */
    public function testItDividesNumbers($number1, $number2, $expectedResult)
    {
        $n1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $n2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $operation = new \MolliePrefix\PrestaShop\Decimal\Operation\Division();
        $result1 = $operation->computeUsingBcMath($n1, $n2, 20);
        $result2 = $operation->computeWithoutBcMath($n1, $n2, 20);
        $this->assertSame($expectedResult, (string) $result1, "Failed asserting {$number1} / {$number2} = {$expectedResult} (BC Math)");
        $this->assertSame($expectedResult, (string) $result2, "Failed asserting {$number1} / {$number2} = {$expectedResult}");
    }
    /**
     * Given a decimal number which is not zero
     * When trying to divide it by zero using BC Math
     * Then we should get a DivisionByZeroException
     *
     * @expectedException PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function testDivisionByZeroUsingBcMathThrowsException()
    {
        (new \MolliePrefix\PrestaShop\Decimal\Operation\Division())->computeUsingBcMath(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0'));
    }
    /**
     * Given a decimal number which is not zero
     * When trying to divide it by zero without BC Math
     * Then we should get a DivisionByZeroException
     *
     * @expectedException PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function testDivisionByZeroWithoutBcMathThrowsException()
    {
        (new \MolliePrefix\PrestaShop\Decimal\Operation\Division())->computeWithoutBcMath(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0'));
    }
    public function provideNumbersToDivide()
    {
        return [
            // 0 as dividend should always yield 0
            ['0', '1', '0'],
            ['0', '1123234.4234234123', '0'],
            ['0', '-1', '0'],
            ['0', '-1123234.4234234123', '0'],
            // 1 as divisor should always yield the dividend
            ['1', '1', '1'],
            ['13524.2342342347262', '1', '13524.2342342347262'],
            // -1 should always yield the inverted dividend
            ['1', '-1', '-1'],
            ['13524.2342342347262', '-1', '-13524.2342342347262'],
            // integer results
            ['2', '1', '2'],
            ['2', '2', '1'],
            ['99', '99', '1'],
            ['198', '99', '2'],
            ['990', '99', '10'],
            ['2', '-1', '-2'],
            ['2', '-2', '-1'],
            ['99', '-99', '-1'],
            ['198', '-99', '-2'],
            ['990', '-99', '-10'],
            ['-2', '-1', '2'],
            ['-2', '-2', '1'],
            ['-99', '-99', '1'],
            ['-198', '-99', '2'],
            ['-990', '-99', '10'],
            ['-2', '1', '-2'],
            ['-2', '2', '-1'],
            ['-99', '99', '-1'],
            ['-198', '99', '-2'],
            ['-990', '99', '-10'],
            // decimal results
            ['1', '100', '0.01'],
            ['1', '3', '0.33333333333333333333'],
            ['1231415', '77', '15992.4025974025974025974'],
            // decimal dividend
            ['12315.73452342341', '27', '456.13831568234851851851'],
            ['0.73452342341', '27', '0.0272045712374074074'],
            // decimal divisor
            ['27', '12315.73452342341', '0.00219231747393129081'],
            ['27', '0.00000012315', '219244823.38611449451887941534'],
        ];
    }
}
