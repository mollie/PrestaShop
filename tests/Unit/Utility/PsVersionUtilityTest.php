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
use Mollie\Utility\PsVersionUtility;

class PsVersionUtilityTest extends BaseTestCase
{
    /**
     * @dataProvider psVersionsProvider
     */
    public function testIsPsVersionGreaterOrEqualTo(string $psVersion, string $higherThen, bool $result)
    {
        $isHigherThenGivenVersion = PsVersionUtility::isPsVersionGreaterOrEqualTo($psVersion, $higherThen);
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
