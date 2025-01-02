<?php

namespace Mollie\Tests\Utility;

use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\NumberIdempotencyProvider;

/**
 * @covers \Mollie\Utility\NumberIdempotencyProvider
 */
final class NumberIdempotencyProviderTest extends BaseTestCase
{
    public function testItGeneratesDifferentKeys(): void
    {
        $provider = new NumberIdempotencyProvider();

        $key1 = $provider->getIdempotencyKey();
        $key2 = $provider->getIdempotencyKey();

        self::assertNotSame($key1, $key2, 'Two consecutive idempotency keys should not be the same.');
    }
}
