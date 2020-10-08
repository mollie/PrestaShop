<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Addition;
class AdditionTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given two decimal numbers
     * When computing the addition operation
     * Then we should get the result of adding those numbers
     *
     * @param string $number1
     * @param string $number2
     * @param string $expectedResult
     *
     * @dataProvider provideNumbersToAdd
     */
    public function testItAddsNumbers($number1, $number2, $expectedResult)
    {
        $n1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $n2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $operation = new \MolliePrefix\PrestaShop\Decimal\Operation\Addition();
        $result1 = $operation->computeUsingBcMath($n1, $n2);
        $result2 = $operation->computeWithoutBcMath($n1, $n2);
        $this->assertSame($expectedResult, (string) $result1, "Failed asserting {$number1} + {$number2} = {$expectedResult} (BC Math)");
        $this->assertSame($expectedResult, (string) $result2, "Failed asserting {$number1} + {$number2} = {$expectedResult}");
    }
    public function provideNumbersToAdd()
    {
        return [
            ['0', '0', '0'],
            ['0', '5', '5'],
            ['0', '5.1', '5.1'],
            ['5', '0', '5'],
            ['5.1', '0', '5.1'],
            ['1.234', '5', '6.234'],
            ['5', '1.234', '6.234'],
            ['10', '0.0000000', '10'],
            ['0.0000000', '10', '10'],
            ['10.01', '0.0000000', '10.01'],
            ['0.0000000', '10.01', '10.01'],
            ['0.0000001', '10.01', '10.0100001'],
            ['9.999999', '9.999999', '19.999998'],
            ['9.999999999999999999', '9.999999999999999999', '19.999999999999999998'],
            ['9223372036854775807.9223372036854775807', '1.01', '9223372036854775808.9323372036854775807'],
            // test adding numbers to negative numbers
            ['-2', '1', '-1'],
            ['-2', '2', '0'],
            ['-2', '3', '1'],
            ['-2', '0.001', '-1.999'],
            // test it delegates to subtraction
            ['2', '-1', '1'],
            ['2', '-2', '0'],
            ['2', '-3', '-1'],
            ['1', '-0.001', '0.999'],
            ['0', '-0.001', '-0.001'],
            ['0', '-1.001', '-1.001'],
            ['-1', '-2', '-3'],
            ['100.12345567433134123236345', '-1.1', '99.02345567433134123236345'],
        ];
    }
}
