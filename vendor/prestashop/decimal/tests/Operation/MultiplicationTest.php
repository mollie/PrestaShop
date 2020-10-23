<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Multiplication;
class MultiplicationTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given two decimal numbers
     * When computing the multiplication operation
     * Then we should get the result of multiplying those numbers
     *
     * @param string $number1
     * @param string $number2
     * @param string $expectedResult
     *
     * @dataProvider provideNumbersToMultiply
     */
    public function testItMultipliesNumbers($number1, $number2, $expectedResult)
    {
        $n1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $n2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $operation = new \MolliePrefix\PrestaShop\Decimal\Operation\Multiplication();
        $result1 = $operation->computeUsingBcMath($n1, $n2);
        $result2 = $operation->computeWithoutBcMath($n1, $n2);
        $this->assertSame($expectedResult, (string) $result1, "Failed asserting {$number1} * {$number2} = {$expectedResult} (BC Math)");
        $this->assertSame($expectedResult, (string) $result2, "Failed asserting {$number1} * {$number2} = {$expectedResult}");
    }
    public function provideNumbersToMultiply()
    {
        return [
            // integer
            'integer 1' => ['1', '1', '1'],
            'integer 2' => ['1', '0', '0'],
            'integer 3' => ['99999999990', '1', '99999999990'],
            'integer 4' => ['1', '99999999990', '99999999990'],
            'integer 5' => ['99999999990', '0', '0'],
            'integer 6' => ['99999999990', '2', '199999999980'],
            'integer 7' => ['99999999990', '10', '999999999900'],
            'integer 8' => ['123456789', '123456789', '15241578750190521'],
            'integer 9' => ['123456789', '-1', '-123456789'],
            'integer 10' => ['-123456789', '-1', '123456789'],
            'integer 11' => ['-123456789', '1', '-123456789'],
            'integer 12' => ['123', '11234667', '1381864041'],
            // decimals
            'decimal 1' => ['99999999990', '0.1', '9999999999'],
            'decimal 2' => ['99999999990', '0.0001', '9999999.999'],
            'decimal 3' => ['99999999990', '0.0002', '19999999.998'],
            'decimal 4' => ['1234.56789', '1234.56789', '1524157.8750190521'],
        ];
    }
}
