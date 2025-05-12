<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Utility;

use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\ArrayUtility;

class ArrayUtilityTest extends BaseTestCase
{
    /**
     * @dataProvider provideArraysForLastElement
     */
    public function testGetLastElement($inputArray, $expected)
    {
        $this->assertSame($expected, ArrayUtility::getLastElement($inputArray));
    }

    public function provideArraysForLastElement()
    {
        return [
            'empty array' => [[], false],
            'single element' => [[42], 42],
            'multiple elements' => [[1, 2, 3], 3],
            'associative array' => [['a' => 'apple', 'b' => 'banana'], 'banana'],
            'nested array' => [['a', [1, 2], 'z'], 'z'],
            'null value in array' => [[null], null],
            'boolean values' => [[true, false], false],
        ];
    }
}
