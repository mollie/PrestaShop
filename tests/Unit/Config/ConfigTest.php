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

namespace Mollie\Tests\Unit\Config;

use Mollie\Config\Config;
use Mollie\Tests\Unit\BaseTestCase;

class ConfigTest extends BaseTestCase
{
    /**
     * @dataProvider provideMethodSupport
     */
    public function testIsMethodSupported(string $methodId, bool $expected)
    {
        $this->assertSame($expected, Config::isMethodSupported($methodId));
    }

    public function provideMethodSupport()
    {
        return [
            'supported: creditcard' => ['creditcard', true],
            'supported: ideal' => ['ideal', true],
            'supported: paypal' => ['paypal', true],
            'supported: klarna' => ['klarna', true],
            'supported: billink' => ['billink', true],
            // A method Mollie may return that the module has no handler for yet.
            'unsupported new method: pointofsale' => ['pointofsale', false],
            'unknown garbage id' => ['not-a-real-method', false],
            'empty id' => ['', false],
        ];
    }
}
