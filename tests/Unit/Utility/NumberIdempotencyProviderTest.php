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
use Mollie\Utility\NumberIdempotencyProvider;

/**
 * @covers \Mollie\Utility\NumberIdempotencyProvider
 */
class NumberIdempotencyProviderTest extends BaseTestCase
{
    public function testItGeneratesDifferentKeys(): void
    {
        $provider = new NumberIdempotencyProvider();

        $key1 = $provider->getIdempotencyKey();
        $key2 = $provider->getIdempotencyKey();

        $this->assertNotSame($key1, $key2, 'Two consecutive idempotency keys should not be the same.');
    }
}
