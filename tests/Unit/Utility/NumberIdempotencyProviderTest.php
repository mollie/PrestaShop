<?php

namespace Mollie\Tests\Utility;

use Mollie\Utility\NumberIdempotencyProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mollie\Utility\NumberIdempotencyProvider
 */
final class NumberIdempotencyProviderTest extends BaseTestCase
{
    public function test_it_generates_different_keys(): void
    {
        $provider = new NumberIdempotencyProvider();

        $key1 = $provider->getIdempotencyKey();
        $key2 = $provider->getIdempotencyKey();

        self::assertNotSame($key1, $key2, 'Two consecutive idempotency keys should not be the same.');
    }
}
