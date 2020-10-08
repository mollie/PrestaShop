<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
use MolliePrefix\PrestaShop\Decimal\Operation\Rounding;
class DecimalNumberTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given a valid number in a string
     * When constructing a Number with it
     * Then it should interpret the sign, decimal and fractional parts correctly
     *
     * @param string $number
     * @param string $expectedSign
     * @param string $expectedInteger
     * @param string $expectedFraction
     * @param string $expectedStr
     *
     * @dataProvider provideValidNumbers
     */
    public function testItInterpretsNumbers($number, $expectedSign, $expectedInteger, $expectedFraction, $expectedStr)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expectedSign, $decimalNumber->getSign(), 'The sign is not as expected');
        $this->assertSame($expectedInteger, $decimalNumber->getIntegerPart(), 'The integer part is not as expected');
        $this->assertSame($expectedFraction, $decimalNumber->getFractionalPart(), 'The fraction part is not as expected');
        $this->assertSame($expectedStr, (string) $decimalNumber, 'The string representation is not as expected');
    }
    /**
     * Given a valid coefficient and exponent
     * When constructing a Number with them
     * Then it should convert them to the expected string
     *
     * @param string $coefficient
     * @param int $exponent
     * @param string $expectedStr
     *
     * @dataProvider provideValidExponents
     */
    public function testItInterpretsExponents($coefficient, $exponent, $expectedStr)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($coefficient, $exponent);
        $this->assertSame($expectedStr, (string) $decimalNumber);
    }
    /**
     * Given an invalid number
     * When constructing a Number with it
     * Then an InvalidArgumentException should be thrown
     *
     * @param mixed $number
     *
     * @dataProvider provideInvalidNumbers
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenGivenInvalidNumber($number)
    {
        new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
    }
    /**
     * Given an invalid coefficient or exponent
     * When constructing a Number with them
     * Then an InvalidArgumentException should be thrown
     *
     * @param mixed $coefficient
     * @param mixed $exponent
     *
     * @dataProvider provideInvalidCoefficients
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenGivenInvalidCoefficientOrExponent($coefficient, $exponent)
    {
        new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($coefficient, $exponent);
    }
    /**
     * Given a Number constructed with a valid number
     * When casting the number to string
     * The resulting string should not include leading nor trailing zeroes
     *
     * @param string $number
     * @param string $expected
     *
     * @dataProvider provideNumbersWithNonSignificantCharacters
     */
    public function testItDropsNonSignificantDigits($number, $expected)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, (string) $decimalNumber);
    }
    /**
     * Given a Number constructed with a valid number
     * When rounding it to a specific precision, using a specific rounding mode
     * The returned string should match the expectation
     *
     * @param string $number
     * @param int $precision Number of decimal characters
     * @param string $mode Rounding mode
     * @param string $expected Expected result
     *
     * @dataProvider providePrecisionTestCases
     */
    public function testPrecision($number, $precision, $mode, $expected)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, (string) $decimalNumber->toPrecision($precision, $mode));
    }
    /**
     * Given a Number constructed with a valid number
     * When rounding it to a specific precision, using a specific rounding mode
     * The returned string should match the expectation
     *
     * @param string $number
     * @param int $precision Number of decimal characters
     * @param string $mode Rounding mode
     * @param string $expected Expected result
     *
     * @dataProvider provideRoundingTestCases
     */
    public function testRounding($number, $precision, $mode, $expected)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, (string) $decimalNumber->round($precision, $mode), "Failed to assert that round {$number} to {$precision} decimals = {$expected}");
    }
    /**
     * Given a Number constructed with a valid number
     * When rounding it to a greater precision than its current one
     * The returned string should have been padded with the proper number of trailing zeroes
     *
     * @param string $number
     * @param int $precision Target precision
     * @param string $expected Expected result
     *
     * @dataProvider provideExtendedPrecisionTestCases
     */
    public function testItExtendsPrecisionAsNeeded($number, $precision, $expected)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $this->assertSame($expected, (string) $decimalNumber->toPrecision($precision), "Failed to assert that fixing {$number} to {$precision} decimals = {$expected}");
    }
    /**
     * Given two instances of Number
     * When comparing the first one with the second one
     * Then the result should be true if the instances are equal, and false otherwise
     *
     * @param DecimalNumber $number1
     * @param DecimalNumber $number2
     * @param string $expected
     *
     * @dataProvider provideEqualityTestCases
     */
    public function testItIsAbleToTellIfEqual($number1, $number2, $expected)
    {
        $this->assertSame($expected, $number1->equals($number2), \sprintf('Failed to assert equality between "%s" and "%s"', $number1, $number2));
    }
    /**
     * Given two numbers
     * When asked if one number is greater than the other
     * Then result should be true if the A > B, and false otherwise
     *
     * @param string $number1
     * @param string $number2
     * @param int $expected
     *
     * @dataProvider provideComparisonTestCases
     */
    public function testIsAbleToTellIfGreaterThan($number1, $number2, $expected)
    {
        $shouldBeGreater = 1 === $expected;
        $number1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $number2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $this->assertSame($number1->isGreaterThan($number2), $shouldBeGreater);
    }
    /**
     * Given two numbers
     * When asked if one number is lower than the other
     * Then result should be true if the A < B, and false otherwise
     *
     * @param string $number1
     * @param string $number2
     * @param int $expected
     *
     * @dataProvider provideComparisonTestCases
     */
    public function testIsAbleToTellIfLowerThan($number1, $number2, $expected)
    {
        $shouldBeLower = -1 === $expected;
        $number1 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number1);
        $number2 = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number2);
        $this->assertSame($number1->isLowerThan($number2), $shouldBeLower);
    }
    /**
     * Given a positive number
     * When it's transformed to negative
     * Then we should get the negative equivalent of the base number
     *
     * @param string $number
     * @param string $expected
     *
     * @dataProvider provideToNegativeTransformationCases
     */
    public function testItTransformsPositiveToNegative($number, $expected)
    {
        $number = (new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number))->toNegative();
        $this->assertSame((string) $number, $expected);
    }
    /**
     * Given a negative number
     * When it's transformed to positive
     * Then we should get the positive equivalent of the base number
     *
     * @param string $number
     * @param string $expected
     *
     * @dataProvider provideToPositiveTransformationCases
     */
    public function testItTransformsNegativeToPositive($number, $expected)
    {
        $number = (new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number))->toPositive();
        $this->assertSame((string) $number, $expected);
    }
    public function provideValidNumbers()
    {
        return [['number' => '0.0', 'expectedSign' => '', 'expectedInteger' => '0', 'expectedFraction' => '0', 'expectedStr' => '0'], ['00000.0', '', '0', '0', '0'], ['0.00000', '', '0', '0', '0'], ['00000.00000', '', '0', '0', '0'], ['0.1', '', '0', '1', '0.1'], ['1.0', '', '1', '0', '1'], ['1.1', '', '1', '1', '1.1'], ['1.234', '', '1', '234', '1.234'], ['0.1245', '', '0', '1245', '0.1245'], ['1', '', '1', '0', '1'], ['01', '', '1', '0', '1'], ['01.0', '', '1', '0', '1'], ['01.01', '', '1', '01', '1.01'], ['10.2345', '', '10', '2345', '10.2345'], '123917549171231.12451028401824' => ['123917549171231.12451028401824', '', '123917549171231', '12451028401824', '123917549171231.12451028401824'], '+12351.49273592' => ['+12351.49273592', '', '12351', '49273592', '12351.49273592'], '-12351.49273592' => ['-12351.49273592', '-', '12351', '49273592', '-12351.49273592'], '-12351' => ['-12351', '-', '12351', '0', '-12351'], '-0' => ['-0', '', '0', '0', '0'], '-01' => ['-01', '-', '1', '0', '-1'], '-01.0' => ['-01.0', '-', '1', '0', '-1'], '-01.01' => ['-01.01', '-', '1', '01', '-1.01'], '0.1e-1' => ['0.1e-1', '', '0', '01', '0.01'], '0.1e-2' => ['0.1e-2', '', '0', '001', '0.001'], '0.1e-3' => ['0.1e-3', '', '0', '0001', '0.0001'], '0.1e-4' => ['0.1e-4', '', '0', '00001', '0.00001'], '0.1e-5' => ['0.1e-5', '', '0', '000001', '0.000001'], '0.01e-1' => ['0.01e-1', '', '0', '001', '0.001'], '123.01e-1' => ['123.01e-1', '', '12', '301', '12.301'], '12301e-4' => ['12301e-4', '', '1', '2301', '1.2301'], '12301e-5' => ['12301e-5', '', '0', '12301', '0.12301'], '12301e-6' => ['12301e-6', '', '0', '012301', '0.012301'], '12301e-7' => ['12301e-7', '', '0', '0012301', '0.0012301'], '12301e-10' => ['12301e-10', '', '0', '0000012301', '0.0000012301'], '12301e+3' => ['12301e+3', '', '12301000', '0', '12301000'], '0.1e+1' => ['0.1e+1', '', '1', '0', '1'], '0.1e+2' => ['0.1e+2', '', '10', '0', '10'], '0.1e+3' => ['0.1e+3', '', '100', '0', '100'], '0.1e+4' => ['0.1e+4', '', '1000', '0', '1000'], '0.1e+5' => ['0.1e+5', '', '10000', '0', '10000'], '123.01e+1' => ['123.01e+1', '', '1230', '1', '1230.1'], '123.01e+5' => ['123.01e+5', '', '12301000', '0', '12301000'], '1.0E+15' => ['1.0E+15', '', '1000000000000000', '0', '1000000000000000'], '-123.0456E+15' => ['-123.0456E+15', '-', '123045600000000000', '0', '-123045600000000000'], '-123.04560E+15' => ['-123.04560E+15', '-', '123045600000000000', '0', '-123045600000000000'], '.1e+2' => ['.1e+2', '', '10', '0', '10'], '-.1e+2' => ['-.1e+2', '-', '10', '0', '-10'], '+.1e+2' => ['+.1e+2', '', '10', '0', '10'], '.01' => ['.01', '', '0', '01', '0.01']];
    }
    public function provideValidExponents()
    {
        return [
            'exponent 0' => ['123456', 0, '123456'],
            'exponent 1' => ['123456', 1, '12345.6'],
            'exponent 2' => ['123456', 2, '1234.56'],
            'exponent 3' => ['123456', 3, '123.456'],
            'exponent 4' => ['123456', 4, '12.3456'],
            'exponent 5' => ['123456', 5, '1.23456'],
            'exponent 6' => ['123456', 6, '0.123456'],
            'exponent 7' => ['123456', 7, '0.0123456'],
            'exponent 8' => ['123456', 8, '0.00123456'],
            'exponent 8 with leading' => ['0123456', 8, '0.00123456'],
            'zero' => ['0', 8, '0'],
            'leading zeroes' => ['00000', 8, '0'],
            'plus leading zeroes' => ['+00000', 8, '0'],
            'minus exponent 0' => ['-123456', 0, '-123456'],
            'minus exponent 1' => ['-123456', 1, '-12345.6'],
            'minus exponent 2' => ['-123456', 2, '-1234.56'],
            'minus exponent 3' => ['-123456', 3, '-123.456'],
            'minus exponent 4' => ['-123456', 4, '-12.3456'],
            'minus exponent 5' => ['-123456', 5, '-1.23456'],
            'minus exponent 6' => ['-123456', 6, '-0.123456'],
            'minus exponent 7' => ['-123456', 7, '-0.0123456'],
            'minus exponent 8' => ['-123456', 8, '-0.00123456'],
            'minus exponent 8 with leading' => ['-0123456', 8, '-0.00123456'],
            'minus zero' => ['-0', 8, '0'],
            'minus leading zeroes' => ['-00000', 8, '0'],
            // trailing zeroes should be dropped on the decimal part
            'trailing zeroes 1' => ['10000000', 4, '1000'],
            'trailing zeroes 2' => ['10002000', 4, '1000.2'],
        ];
    }
    public function provideInvalidNumbers()
    {
        return ['bool false' => [\false], 'bool true' => [\true], 'empty string' => [''], 'NaN' => ['asd'], 'NaN with dot' => ['asd.foo'], 'NaN with comma' => ['asd,foo'], 'array' => [array()], 'null' => [null], '1.' => ['1.']];
    }
    public function provideInvalidCoefficients()
    {
        return ['bool false' => [\false, 0], 'bool true' => [\true, 0], 'empty string' => ['', 0], 'NaN' => ['asd', 0], 'NaN with dot' => ['asd.foo', 0], 'NaN with comma' => ['asd,foo', 0], 'array' => [array(), 0], 'null' => [null, 0], 'negative coefficient' => ['123', -5]];
    }
    public function provideNumbersWithNonSignificantCharacters()
    {
        return [['01', '1'], ['010', '10'], ['000010', '10'], ['10.1', '10.1'], ['10.10', '10.1'], ['10.11230000', '10.1123'], ['0000010.11230000', '10.1123'], ['+0000010.11230000', '10.1123'], ['-01', '-1'], ['-010', '-10'], ['-000010', '-10'], ['-10.1', '-10.1'], ['-10.10', '-10.1'], ['-10.11230000', '-10.1123'], ['-0000010.11230000', '-10.1123']];
    }
    public function provideExtendedPrecisionTestCases()
    {
        return [['1.23456789', 8, '1.23456789'], ['1.23456789', 9, '1.234567890'], ['1.23456789', 10, '1.2345678900']];
    }
    public function provideRoundingTestCases()
    {
        return [
            'truncate 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1'],
            'truncate 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2'],
            'truncate 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23'],
            'truncate 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.234'],
            'truncate 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2345'],
            'truncate 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456'],
            'truncate 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.234567'],
            'truncate 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2345678'],
            'truncate 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456789'],
            // does not add trailing zeroes
            'truncate 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456789'],
            'truncate 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456789'],
            'truncate zeroes 1' => ['1.00000001', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1'],
            'truncate zeroes 2' => ['1.00000001', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.00000001'],
            'ceil 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '2'],
            'ceil 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.3'],
            'ceil 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.24'],
            'ceil 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.235'],
            'ceil 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.2346'],
            'ceil 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23457'],
            'ceil 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.234568'],
            'ceil 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.2345679'],
            'ceil 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23456789'],
            // does not add trailing zeroes
            'ceil 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23456789'],
            'ceil 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23456789'],
            'round half up 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1'],
            'round half up 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2'],
            'round half up 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23'],
            'round half up 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.235'],
            'round half up 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2346'],
            'round half up 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23457'],
            'round half up 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.234568'],
            'round half up 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2345679'],
            'round half up 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23456789'],
            // does not add trailing zeroes
            'round half up 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23456789'],
            'round half up 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23456789'],
        ];
    }
    public function providePrecisionTestCases()
    {
        return [
            'truncate 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1'],
            'truncate 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2'],
            'truncate 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23'],
            'truncate 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.234'],
            'truncate 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2345'],
            'truncate 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456'],
            'truncate 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.234567'],
            'truncate 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2345678'],
            'truncate 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.23456789'],
            // adds trailing zeroes
            'truncate 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.234567890'],
            'truncate 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.2345678900'],
            // keeps trailing zeroes
            'truncate zeroes 1' => ['1.00000001', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.000'],
            'truncate zeroes 2' => ['1.00000001', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, '1.00000001'],
            'ceil 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '2'],
            'ceil 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.3'],
            'ceil 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.24'],
            'ceil 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.235'],
            'ceil 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.2346'],
            'ceil 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23457'],
            'ceil 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.234568'],
            'ceil 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.2345679'],
            'ceil 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.23456789'],
            'ceil zeroes' => ['1.00000001', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.0000001'],
            // adds trailing zeroes
            'ceil 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.234567890'],
            'ceil 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, '1.2345678900'],
            'round half up 0' => ['1.23456789', 0, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1'],
            'round half up 1' => ['1.23456789', 1, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2'],
            'round half up 2' => ['1.23456789', 2, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23'],
            'round half up 3' => ['1.23456789', 3, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.235'],
            'round half up 4' => ['1.23456789', 4, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2346'],
            'round half up 5' => ['1.23456789', 5, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23457'],
            'round half up 6' => ['1.23456789', 6, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.234568'],
            'round half up 7' => ['1.23456789', 7, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2345679'],
            'round half up 8' => ['1.23456789', 8, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.23456789'],
            // adds trailing zeroes
            'round half up 9' => ['1.23456789', 9, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.234567890'],
            'round half up 10' => ['1.23456789', 10, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, '1.2345678900'],
        ];
    }
    public function provideEqualityTestCases()
    {
        return [[new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0', 5), \true], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0.1234'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1234', 4), \true], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1234.01'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('123401', 2), \true], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-0'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('0'), \true], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-1234.01'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-123401', 2), \true], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-1234.01'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('123401', 2), \false], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1234.01'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-123401', 2), \false], [new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1234.01'), new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('-1234.01'), \false]];
    }
    public function provideComparisonTestCases()
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
    public function provideToNegativeTransformationCases()
    {
        return [['1.2345', '-1.2345'], ['-1.2345', '-1.2345'], ['0', '0'], ['-0', '0']];
    }
    public function provideToPositiveTransformationCases()
    {
        return [['-1.2345', '1.2345'], ['1.2345', '1.2345'], ['0', '0'], ['-0', '0']];
    }
}
