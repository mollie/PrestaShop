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

namespace Mollie\Utility\ApplePayDirect;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Authorization rules that bind Apple Pay Direct cart operations to the caller's session.
 *
 * The product-page flow mints an anonymous cart server-side and the cart-page flow reuses
 * the shopper's session cart, so there is no logged-in customer to bind against. These pure
 * decisions keep the controller from ever trusting a raw, attacker-supplied cart id.
 */
class CartOwnershipUtility
{
    /**
     * Decides which cart id the payment-session request may reuse.
     *
     * Only the shopper's own session cart may be reused (cart-page flow); any other supplied
     * value yields 0, forcing a fresh empty cart to be minted (product-page flow). This stops
     * an attacker from binding a foreign cart to their session.
     */
    public static function resolveReusableCartId(int $requestedCartId, int $sessionCartId): int
    {
        return $requestedCartId > 0 && $requestedCartId === $sessionCartId ? $sessionCartId : 0;
    }

    /**
     * A cart id is authorized when it is either the caller's own session cart (cart-page flow)
     * or the Apple Pay cart previously bound to this session (product-page flow).
     */
    public static function isCartAuthorized(int $cartId, int $sessionCartId, int $boundCartId): bool
    {
        if ($cartId <= 0) {
            return false;
        }

        return $cartId === $sessionCartId || $cartId === $boundCartId;
    }
}
