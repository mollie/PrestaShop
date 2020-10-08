<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Subtraction;
class SubtractionTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given two decimal numbers
     * When computing the subtraction operation
     * Then we should get the result of subtracting those numbers
     *
     * @param string $number1
     * @param string $number2
     * @param string $expectedResult
     *
     * @dataProvider provideNumbersToSubtract
     */
    public function testItSubtractsNumbers($number1, $number2, $expectedResult)
    {
        $n1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $n2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $operation = new \MolliePrefix\PrestaShop\Decimal\Operation\Subtraction();
        $result1 = $operation->computeUsingBcMath($n1, $n2);
        $result2 = $operation->computeWithoutBcMath($n1, $n2);
        $this->assertSame($expectedResult, (string) $result1, "Failed asserting {$number1} - {$number2} = {$expectedResult} (BC Math)");
        $this->assertSame($expectedResult, (string) $result2, "Failed asserting {$number1} - {$number2} = {$expectedResult}");
    }
    public function provideNumbersToSubtract()
    {
        return [['0', '0', '0'], ['0', '5', '-5'], ['0', '5.1', '-5.1'], ['2', '1', '1'], ['1', '2', '-1'], ['-1', '2', '-3'], ['1', '-2', '3'], ['-1', '-2', '1'], ['5', '0', '5'], ['5.1', '0', '5.1'], ['1.234', '5', '-3.766'], ['5', '1.234', '3.766'], ['10', '0.0000000', '10'], ['0.0000000', '10', '-10'], ['10.01', '0.0000000', '10.01'], ['1', '0.0000001', '0.9999999'], ['1', '0.0000001', '0.9999999'], ['0', '0.0000001', '-0.0000001'], ['0.0000001', '0.0000001', '0'], ['0.0000001', '0.0000002', '-0.0000001'], ['0.0000000', '10.01', '-10.01'], ['0.0000001', '10.01', '-10.0099999'], ['9.999999', '9.999999', '0'], ['9.999999999999999999', '9.999999999999999999', '0'], ['9.999999999999999999', '9.999999999999999998', '0.000000000000000001'], ['9223372036854775807.9223372036854775807', '1.01', '9223372036854775806.9123372036854775807']];
    }
}
