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
use Mollie\Utility\HookRegistrationUtility;

class HookRegistrationUtilityTest extends BaseTestCase
{
    public function testKeepsDisplayPaymentEuBelowPrestaShop17()
    {
        $this->assertSame(
            ['paymentOptions', 'displayPaymentEU'],
            HookRegistrationUtility::installableHooks(['paymentOptions', 'displayPaymentEU'], '1.6.1.24')
        );
    }

    public function testDropsDisplayPaymentEuFromPrestaShop17Onwards()
    {
        $this->assertSame(
            ['paymentOptions'],
            HookRegistrationUtility::installableHooks(['paymentOptions', 'displayPaymentEU'], '1.7.0.0')
        );
    }

    public function testDropsDisplayPaymentEuOnPrestaShop9()
    {
        $this->assertSame(
            ['paymentOptions'],
            HookRegistrationUtility::installableHooks(['paymentOptions', 'displayPaymentEU'], '9.1.4')
        );
    }

    public function testKeepsTheDeclaredHookOrder()
    {
        $this->assertSame(
            ['displayBackOfficeHeader', 'paymentOptions', 'displayAdminOrder'],
            HookRegistrationUtility::installableHooks(
                ['displayBackOfficeHeader', 'paymentOptions', 'displayAdminOrder'],
                '9.1.4'
            )
        );
    }

    public function testReturnsNothingWhenThereAreNoHooks()
    {
        $this->assertSame([], HookRegistrationUtility::installableHooks([], '9.1.4'));
    }
}
