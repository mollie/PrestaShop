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

namespace Mollie\Tests\Unit\Service;

use Mollie\Adapter\Shop;
use Mollie\Service\MultistoreSettingsContextGuard;
use PHPUnit\Framework\TestCase;
use Shop as PrestaShop;

class MultistoreSettingsContextGuardTest extends TestCase
{
    /**
     * @dataProvider contextProvider
     */
    public function testCanEditSettingsOnlyInSingleShopContext(int $context, bool $expected)
    {
        $shop = $this->createMock(Shop::class);
        $shop->method('getContext')->willReturn($context);

        $guard = new MultistoreSettingsContextGuard($shop);

        $this->assertSame($expected, $guard->canEditSettings());
    }

    public function contextProvider(): array
    {
        return [
            'single shop can edit' => [PrestaShop::CONTEXT_SHOP, true],
            'shop group is blocked' => [PrestaShop::CONTEXT_GROUP, false],
            'all stores is blocked' => [PrestaShop::CONTEXT_ALL, false],
        ];
    }
}
