<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Test\Operation;

use MolliePrefix\PrestaShop\Decimal\Operation\Rounding;
use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
class RoundingTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * Given decimal number
     * When rounding it using an undefined rounding mode
     * An InvalidArgumentException should be thrown
     *
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionIfRoundingModeIsInvalid()
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1.2345');
        $rounding = new \MolliePrefix\PrestaShop\Decimal\Operation\Rounding();
        $rounding->compute($decimalNumber, 2, 'foobar');
    }
    /**
     * Given decimal number
     * When rounding to an invalid precision
     * An InvalidArgumentException should be thrown
     *
     * @param mixed $precision
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider provideInvalidPrecision
     */
    public function testItThrowsExceptionIfPrecisionIsInvalid($precision)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber('1.2345');
        $rounding = new \MolliePrefix\PrestaShop\Decimal\Operation\Rounding();
        $rounding->compute($decimalNumber, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR);
    }
    /**
     * Given a decimal number
     * When truncating the number to a target precision
     * Then the resulting number should have at most <precision> number of decimals
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideTruncateTestCases
     */
    public function testItTruncatesNumbers($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_TRUNCATE, $expected);
    }
    /**
     * Given a decimal number
     * When rounding using ceiling to a target precision
     * Then the number should have at most <precision> number of decimals
     * And it should be rounded to positive infinity if its original precision was larger than the target one
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideCeilTestCases
     */
    public function testItPerformsCeilRounding($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL, $expected);
    }
    /**
     * Given a decimal number
     * When rounding using floor to a target precision
     * Then the number should have at most <precision> number of decimals
     * And it should be rounded to negative infinity if its original precision was larger than the target one
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideFloorTestCases
     */
    public function testItPerformsFloorRounding($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR, $expected);
    }
    /**
     * Given a decimal number
     * When rounding using half-up to a target precision
     * Then
     * - the number should have at most <precision> number of decimals
     * - and it should be rounded
     *     - away from zero if the digit to the right of its last significant digit was >= 5
     *     - towards zero if the digit to the right of its last significant digit was < 5
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideHalfUpTestCases
     */
    public function testItPerformsHalfUpRounding($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP, $expected);
    }
    /**
     * Given a decimal number
     * When rounding using half-down to a target precision
     * Then
     * - the number should have at most <precision> number of decimals
     * - and it should be rounded
     *     - away from zero if the digit to the right of its last significant digit was > 5
     *     - towards zero if the digit to the right of its last significant digit was <= 5
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideHalfDownTestCases
     */
    public function testItPerformsHalfDownRounding($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN, $expected);
    }
    /**
     * Given a decimal number
     * When rounding using half-even to a target precision
     * Then
     * - the number should have at most <precision> number of decimals
     * - and it should be rounded
     *     - away from zero
     *         - if the digit to the right of its last significant digit was > 5
     *         - or if the last significant digit is odd and the digit to the right of it is 5
     *     - towards zero
     *         - if the digit to the right of its last significant digit was < 5
     *         - or if the last significant digit is even and the digit to the right of it is 5
     *
     * @param string $number
     * @param int $precision
     * @param string $expected
     *
     * @dataProvider provideHalfEvenTestCases
     */
    public function testItPerformsHalfEvenRounding($number, $precision, $expected)
    {
        $this->roundNumber($number, $precision, \MolliePrefix\PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN, $expected);
    }
    /**
     * Test rounding a number to a target precision using a specific rounding mode.
     *
     * Given
     * - A Decimal\Number constructed with a valid number
     * - A target precision
     * - A rounding mode
     * - And an expected result
     *
     * When rounding it to a specific precision, using a specific rounding mode
     * Then the returned string should match the expected string
     *
     * @param $number
     * @param $precision
     * @param $mode
     * @param $expected
     */
    public function roundNumber($number, $precision, $mode, $expected)
    {
        $decimalNumber = new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number);
        $rounding = new \MolliePrefix\PrestaShop\Decimal\Operation\Rounding();
        $result = $rounding->compute($decimalNumber, $precision, $mode);
        $this->assertSame($expected, (string) $result);
    }
    public function provideTruncateTestCases()
    {
        return [
            '0 digits' => ['1.23456789', 0, '1'],
            '1 digit' => ['1.23456789', 1, '1.2'],
            '2 digits' => ['1.23456789', 2, '1.23'],
            '3 digits' => ['1.23456789', 3, '1.234'],
            '4 digits' => ['1.23456789', 4, '1.2345'],
            '5 digits' => ['1.23456789', 5, '1.23456'],
            '6 digits' => ['1.23456789', 6, '1.234567'],
            '7 digits' => ['1.23456789', 7, '1.2345678'],
            '8 digits' => ['1.23456789', 8, '1.23456789'],
            // should not add trailing zeroes
            '9 digits' => ['1.23456789', 9, '1.23456789'],
            '10 digits' => ['1.23456789', 10, '1.23456789'],
            // negative numbers
            '0 digits, negative' => ['-1.23456789', 0, '-1'],
            '1 digit, negative' => ['-1.23456789', 1, '-1.2'],
            '2 digits, negative' => ['-1.23456789', 2, '-1.23'],
            '3 digits, negative' => ['-1.23456789', 3, '-1.234'],
            '4 digits, negative' => ['-1.23456789', 4, '-1.2345'],
            '5 digits, negative' => ['-1.23456789', 5, '-1.23456'],
            '6 digits, negative' => ['-1.23456789', 6, '-1.234567'],
            '7 digits, negative' => ['-1.23456789', 7, '-1.2345678'],
            '8 digits, negative' => ['-1.23456789', 8, '-1.23456789'],
            // should not add trailing zeroes
            '9 digits, negative' => ['-1.23456789', 9, '-1.23456789'],
            '10 digits, negative' => ['-1.23456789', 10, '-1.23456789'],
        ];
    }
    public function provideCeilTestCases()
    {
        return [
            '0 digits' => ['1.23456789', 0, '2'],
            '1 digit' => ['1.23456789', 1, '1.3'],
            '2 digits' => ['1.23456789', 2, '1.24'],
            '3 digits' => ['1.23456789', 3, '1.235'],
            '4 digits' => ['1.23456789', 4, '1.2346'],
            '5 digits' => ['1.23456789', 5, '1.23457'],
            '6 digits' => ['1.23456789', 6, '1.234568'],
            '7 digits' => ['1.23456789', 7, '1.2345679'],
            '8 digits' => ['1.23456789', 8, '1.23456789'],
            // should not add trailing zeroes
            '9 digits' => ['1.23456789', 9, '1.23456789'],
            '10 digits' => ['1.23456789', 10, '1.23456789'],
            // negative numbers
            '0 digits, negative' => ['-1.23456789', 0, '-1'],
            '1 digit, negative' => ['-1.23456789', 1, '-1.2'],
            '2 digits, negative' => ['-1.23456789', 2, '-1.23'],
            '3 digits, negative' => ['-1.23456789', 3, '-1.234'],
            '4 digits, negative' => ['-1.23456789', 4, '-1.2345'],
            '5 digits, negative' => ['-1.23456789', 5, '-1.23456'],
            '6 digits, negative' => ['-1.23456789', 6, '-1.234567'],
            '7 digits, negative' => ['-1.23456789', 7, '-1.2345678'],
            '8 digits, negative' => ['-1.23456789', 8, '-1.23456789'],
            // should not add trailing zeroes
            '9 digits, negative' => ['-1.23456789', 9, '-1.23456789'],
            '10 digits, negative' => ['-1.23456789', 10, '-1.23456789'],
        ];
    }
    public function provideFloorTestCases()
    {
        return [
            '0 digits' => ['1.23456789', 0, '1'],
            '1 digit' => ['1.23456789', 1, '1.2'],
            '2 digits' => ['1.23456789', 2, '1.23'],
            '3 digits' => ['1.23456789', 3, '1.234'],
            '4 digits' => ['1.23456789', 4, '1.2345'],
            '5 digits' => ['1.23456789', 5, '1.23456'],
            '6 digits' => ['1.23456789', 6, '1.234567'],
            '7 digits' => ['1.23456789', 7, '1.2345678'],
            '8 digits' => ['1.23456789', 8, '1.23456789'],
            // should not add trailing zeroes
            '9 digits' => ['1.23456789', 9, '1.23456789'],
            '10 digits' => ['1.23456789', 10, '1.23456789'],
            // negative numbers
            '0 digits, negative' => ['-1.23456789', 0, '-2'],
            '1 digit, negative' => ['-1.23456789', 1, '-1.3'],
            '2 digits, negative' => ['-1.23456789', 2, '-1.24'],
            '3 digits, negative' => ['-1.23456789', 3, '-1.235'],
            '4 digits, negative' => ['-1.23456789', 4, '-1.2346'],
            '5 digits, negative' => ['-1.23456789', 5, '-1.23457'],
            '6 digits, negative' => ['-1.23456789', 6, '-1.234568'],
            '7 digits, negative' => ['-1.23456789', 7, '-1.2345679'],
            '8 digits, negative' => ['-1.23456789', 8, '-1.23456789'],
            // should not add trailing zeroes
            '9 digits, negative' => ['-1.23456789', 9, '-1.23456789'],
            '10 digits, negative' => ['-1.23456789', 10, '-1.23456789'],
        ];
    }
    public function provideHalfUpTestCases()
    {
        return [
            '0 digits' => ['1.23456789', 0, '1'],
            '1 digit' => ['1.23456789', 1, '1.2'],
            '2 digits' => ['1.23456789', 2, '1.23'],
            '3 digits' => ['1.23456789', 3, '1.235'],
            '4 digits' => ['1.23456789', 4, '1.2346'],
            '5 digits' => ['1.23456789', 5, '1.23457'],
            '6 digits' => ['1.23456789', 6, '1.234568'],
            '7 digits' => ['1.23456789', 7, '1.2345679'],
            '8 digits' => ['1.23456789', 8, '1.23456789'],
            // should not add trailing zeroes
            '9 digits' => ['1.23456789', 9, '1.23456789'],
            '10 digits' => ['1.23456789', 10, '1.23456789'],
            // negative numbers
            '0 digits, negative' => ['-1.23456789', 0, '-1'],
            '1 digit, negative' => ['-1.23456789', 1, '-1.2'],
            '2 digits, negative' => ['-1.23456789', 2, '-1.23'],
            '3 digits, negative' => ['-1.23456789', 3, '-1.235'],
            '4 digits, negative' => ['-1.23456789', 4, '-1.2346'],
            '5 digits, negative' => ['-1.23456789', 5, '-1.23457'],
            '6 digits, negative' => ['-1.23456789', 6, '-1.234568'],
            '7 digits, negative' => ['-1.23456789', 7, '-1.2345679'],
            '8 digits, negative' => ['-1.23456789', 8, '-1.23456789'],
            // should not add trailing zeroes
            '9 digits, negative' => ['-1.23456789', 9, '-1.23456789'],
            '10 digits, negative' => ['-1.23456789', 10, '-1.23456789'],
        ];
    }
    public function provideHalfDownTestCases()
    {
        return [
            '0 digits' => ['1.23456789', 0, '1'],
            '1 digit' => ['1.23456789', 1, '1.2'],
            '2 digits' => ['1.23456789', 2, '1.23'],
            '3 digits' => ['1.23456789', 3, '1.234'],
            '4 digits' => ['1.23456789', 4, '1.2346'],
            '5 digits' => ['1.23456789', 5, '1.23457'],
            '6 digits' => ['1.23456789', 6, '1.234568'],
            '7 digits' => ['1.23456789', 7, '1.2345679'],
            '8 digits' => ['1.23456789', 8, '1.23456789'],
            // should not add trailing zeroes
            '9 digits' => ['1.23456789', 9, '1.23456789'],
            '10 digits' => ['1.23456789', 10, '1.23456789'],
            // negative numbers
            '0 digits, negative' => ['-1.23456789', 0, '-1'],
            '1 digit, negative' => ['-1.23456789', 1, '-1.2'],
            '2 digits, negative' => ['-1.23456789', 2, '-1.23'],
            '3 digits, negative' => ['-1.23456789', 3, '-1.234'],
            '4 digits, negative' => ['-1.23456789', 4, '-1.2346'],
            '5 digits, negative' => ['-1.23456789', 5, '-1.23457'],
            '6 digits, negative' => ['-1.23456789', 6, '-1.234568'],
            '7 digits, negative' => ['-1.23456789', 7, '-1.2345679'],
            '8 digits, negative' => ['-1.23456789', 8, '-1.23456789'],
            // should not add trailing zeroes
            '9 digits, negative' => ['-1.23456789', 9, '-1.23456789'],
            '10 digits, negative' => ['-1.23456789', 10, '-1.23456789'],
        ];
    }
    public function provideHalfEvenTestCases()
    {
        return [
            'round even' => ['2.5', 0, '2'],
            'round odd' => ['1.5', 0, '2'],
            '0 digits' => ['1.1525354556575859505', 0, '1'],
            '1 digit' => ['1.1525354556575859505', 1, '1.2'],
            '2 digits' => ['1.1525354556575859505', 2, '1.15'],
            '3 digits' => ['1.1525354556575859505', 3, '1.152'],
            '4 digits' => ['1.1525354556575859505', 4, '1.1525'],
            '5 digits' => ['1.1525354556575859505', 5, '1.15254'],
            '6 digits' => ['1.1525354556575859505', 6, '1.152535'],
            '7 digits' => ['1.1525354556575859505', 7, '1.1525354'],
            '8 digits' => ['1.1525354556575859505', 8, '1.15253546'],
            '9 digits' => ['1.1525354556575859505', 9, '1.152535456'],
            '10 digits' => ['1.1525354556575859505', 10, '1.1525354556'],
            '11 digits' => ['1.1525354556575859505', 11, '1.15253545566'],
            '12 digits' => ['1.1525354556575859505', 12, '1.152535455658'],
            '13 digits' => ['1.1525354556575859505', 13, '1.1525354556576'],
            '14 digits' => ['1.1525354556575859505', 14, '1.15253545565758'],
            '15 digits' => ['1.1525354556575859505', 15, '1.152535455657586'],
            '16 digits' => ['1.1525354556575859505', 16, '1.152535455657586'],
            '17 digits' => ['1.1525354556575859505', 17, '1.15253545565758595'],
            '18 digits' => ['1.1525354556575859505', 18, '1.15253545565758595'],
            '19 digits' => ['1.1525354556575859505', 19, '1.1525354556575859505'],
            // should not add trailing zeroes
            '20 digits' => ['1.1525354556575859505', 20, '1.1525354556575859505'],
            // negative numbers
            'round even, negative' => ['-2.5', 0, '-2'],
            'round odd, negative' => ['-1.5', 0, '-2'],
            '0 digits, negative' => ['-1.1525354556575859505', 0, '-1'],
            '1 digit, negative' => ['-1.1525354556575859505', 1, '-1.2'],
            '2 digits, negative' => ['-1.1525354556575859505', 2, '-1.15'],
            '3 digits, negative' => ['-1.1525354556575859505', 3, '-1.152'],
            '4 digits, negative' => ['-1.1525354556575859505', 4, '-1.1525'],
            '5 digits, negative' => ['-1.1525354556575859505', 5, '-1.15254'],
            '6 digits, negative' => ['-1.1525354556575859505', 6, '-1.152535'],
            '7 digits, negative' => ['-1.1525354556575859505', 7, '-1.1525354'],
            '8 digits, negative' => ['-1.1525354556575859505', 8, '-1.15253546'],
            '11 digits, negative' => ['-1.1525354556575859505', 11, '-1.15253545566'],
            '12 digits, negative' => ['-1.1525354556575859505', 12, '-1.152535455658'],
            '13 digits, negative' => ['-1.1525354556575859505', 13, '-1.1525354556576'],
            '14 digits, negative' => ['-1.1525354556575859505', 14, '-1.15253545565758'],
            '15 digits, negative' => ['-1.1525354556575859505', 15, '-1.152535455657586'],
            '16 digits, negative' => ['-1.1525354556575859505', 16, '-1.152535455657586'],
            '17 digits, negative' => ['-1.1525354556575859505', 17, '-1.15253545565758595'],
            '18 digits, negative' => ['-1.1525354556575859505', 18, '-1.15253545565758595'],
            '19 digits, negative' => ['-1.1525354556575859505', 19, '-1.1525354556575859505'],
            // should not add trailing zeroes
            '20 digits, negative' => ['-1.1525354556575859505', 20, '-1.1525354556575859505'],
        ];
    }
    public function provideInvalidPrecision()
    {
        return [[-1], ['foo'], [\true], [array()]];
    }
}
