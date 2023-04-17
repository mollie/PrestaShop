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

namespace Utility;

use Mollie\Utility\PsVersionUtility;
use PHPUnit\Framework\TestCase;

class PsVersionUtilityTest extends TestCase
{
    /**
     * @dataProvider psVersionsProvider
     */
    public function testIsPsVersionHigherOrEqualsThan(string $psVersion, string $higherThen, bool $result)
    {
        $isHigherThenGivenVersion = PsVersionUtility::isPsVersionHigherOrEqualsThan($psVersion, $higherThen);
        $this->assertEquals($result, $isHigherThenGivenVersion);
    }

    public function psVersionsProvider()
    {
        return [
            'case1' => [
                'psVersion' => '1.7.5.0',
                'higherThen' => '1.7.4.0',
                'result' => true,
            ],
            'case2' => [
                'psVersion' => '1.7.4.0',
                'higherThen' => '1.7.5.0',
                'result' => false,
            ],
            'case3' => [
                'psVersion' => '1.7.5.0',
                'higherThen' => '1.7.5.0',
                'result' => true,
            ],
        ];
    }
}
