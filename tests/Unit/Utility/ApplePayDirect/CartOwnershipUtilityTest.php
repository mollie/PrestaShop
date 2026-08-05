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

use Mollie\Utility\ApplePayDirect\CartOwnershipUtility;
use PHPUnit\Framework\TestCase;

class CartOwnershipUtilityTest extends TestCase
{
    /**
     * @dataProvider resolveReusableCartIdDataProvider
     */
    public function testResolveReusableCartId(int $requestedCartId, int $sessionCartId, int $expected)
    {
        $this->assertSame($expected, CartOwnershipUtility::resolveReusableCartId($requestedCartId, $sessionCartId));
    }

    public function resolveReusableCartIdDataProvider(): array
    {
        return [
            'cart-page flow reuses own session cart' => [
                'requestedCartId' => 42,
                'sessionCartId' => 42,
                'expected' => 42,
            ],
            'product-page flow sends no cart, forces mint' => [
                'requestedCartId' => 0,
                'sessionCartId' => 55,
                'expected' => 0,
            ],
            'foreign cart id is never reused, forces mint' => [
                'requestedCartId' => 999,
                'sessionCartId' => 42,
                'expected' => 0,
            ],
            'no session cart yet, forces mint' => [
                'requestedCartId' => 42,
                'sessionCartId' => 0,
                'expected' => 0,
            ],
            'negative requested id is rejected' => [
                'requestedCartId' => -5,
                'sessionCartId' => -5,
                'expected' => 0,
            ],
        ];
    }

    /**
     * @dataProvider isCartAuthorizedDataProvider
     */
    public function testIsCartAuthorized(int $cartId, int $sessionCartId, int $boundCartId, bool $expected)
    {
        $this->assertSame($expected, CartOwnershipUtility::isCartAuthorized($cartId, $sessionCartId, $boundCartId));
    }

    public function isCartAuthorizedDataProvider(): array
    {
        return [
            'matches session cart (cart-page flow)' => [
                'cartId' => 42,
                'sessionCartId' => 42,
                'boundCartId' => 0,
                'expected' => true,
            ],
            'matches bound cart (product-page flow)' => [
                'cartId' => 77,
                'sessionCartId' => 42,
                'boundCartId' => 77,
                'expected' => true,
            ],
            'foreign cart matching neither is rejected' => [
                'cartId' => 999,
                'sessionCartId' => 42,
                'boundCartId' => 77,
                'expected' => false,
            ],
            'attacker with no session cart nor binding is rejected' => [
                'cartId' => 999,
                'sessionCartId' => 0,
                'boundCartId' => 0,
                'expected' => false,
            ],
            'zero cart id is rejected' => [
                'cartId' => 0,
                'sessionCartId' => 0,
                'boundCartId' => 0,
                'expected' => false,
            ],
            'negative cart id is rejected even if it matches' => [
                'cartId' => -1,
                'sessionCartId' => -1,
                'boundCartId' => -1,
                'expected' => false,
            ],
        ];
    }
}
