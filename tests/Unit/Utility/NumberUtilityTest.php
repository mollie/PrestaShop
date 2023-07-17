<?php

namespace Mollie\Tests\Unit\Utility;

use Mollie\Utility\NumberUtility;
use PHPUnit\Framework\TestCase;

class NumberUtilityTest extends TestCase
{
    public function testItSuccessfullySetsDecimalPrecision()
    {
        $result = NumberUtility::toPrecision(6.56739, 2);

        $this->assertEquals(6.57, $result);

        $result = NumberUtility::toPrecision(6.56139, 2);

        $this->assertEquals(6.56, $result);
    }

    public function testItSuccessfullyMultipliesNumber()
    {
        $result = NumberUtility::times(6.56, 2.57);

        $this->assertEquals(16.8592, $result);

        $result = NumberUtility::times(6.56, 2.57, 2);

        $this->assertEquals(16.86, $result);

        $result = NumberUtility::times(0, 0);

        $this->assertEquals(0, $result);
    }

    public function testItSuccessfullyDividesNumber()
    {
        $result = NumberUtility::divide(6.56, 2.5);

        $this->assertEquals(2.624, $result);

        $result = NumberUtility::divide(6.56, 2.5, 2);

        $this->assertEquals(2.62, $result);
    }

    public function testItSuccessfullyChecksIsGreaterThan()
    {
        $result = NumberUtility::isGreaterThan(6.56, 6.556);

        $this->assertEquals(true, $result);
    }
}
