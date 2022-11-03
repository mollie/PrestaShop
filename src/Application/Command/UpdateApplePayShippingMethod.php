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

namespace Mollie\Application\Command;

final class UpdateApplePayShippingMethod
{
    /**
     * @var int
     */
    private $carrierId;
    /**
     * @var int
     */
    private $cartId;

    public function __construct(int $carrierId, int $cartId)
    {
        $this->carrierId = $carrierId;
        $this->cartId = $cartId;
    }

    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }
}
