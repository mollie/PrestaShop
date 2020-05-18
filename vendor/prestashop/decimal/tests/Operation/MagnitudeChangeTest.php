<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace _PhpScoper5ea00cc67502b\PrestaShop\Decimal\Test\Operation;

use _PhpScoper5ea00cc67502b\PHPUnit_Framework_TestCase;
use _PhpScoper5ea00cc67502b\PrestaShop\Decimal\Number;
use _PhpScoper5ea00cc67502b\PrestaShop\Decimal\Operation\MagnitudeChange;
class MagnitudeChangeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Given a decimal number
     * When changing its magnitude to a specific exponent
     * Then we should get the result of multiplying it by 10^exponent
     * (Same as shifting the decimal dot to the left or to the right by "exponent" characters)
     *
     * @param string $number
     * @param int $exponent
     * @param string $expected
     *
     * @dataProvider provideTestCases
     */
    public function testItChangesMagnitude($number, $exponent, $expected)
    {
        $n = new Number($number);
        $result = (new MagnitudeChange())->compute($n, $exponent);
        $this->assertSame($expected, (string) $result);
    }
    public function provideTestCases()
    {
        return [['123.45678', 0, '123.45678'], ['123.45678', 1, '1234.5678'], ['123.45678', 2, '12345.678'], ['123.45678', 3, '123456.78'], ['123.45678', 6, '123456780'], ['123.45678', 8, '12345678000'], ['123.45678', -1, '12.345678'], ['123.45678', -2, '1.2345678'], ['123.45678', -3, '0.12345678'], ['123.45678', -6, '0.00012345678'], ['123.45678', -8, '0.0000012345678']];
    }
}
