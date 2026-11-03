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

namespace Mollie\Service;

use Cart;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CartRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The Apple Pay Direct sheet moves the cart lines onto its soft-deleted temporary address.
 * When the sheet is cancelled, core only resets the cart's own address on the next request
 * (Cart::checkAndUpdateAddresses()) and Cart::updateAddressId() later moves lines from that
 * reset address only, so the lines stay behind and PS 1.7/8 checkout finds no carrier.
 */
class ApplePayCartLinesAddressService
{
    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressRepository = $addressRepository;
    }

    public function moveLinesOffTemporaryAddresses(Cart $cart): void
    {
        $cartId = (int) $cart->id;
        $deliveryAddressId = (int) $cart->id_address_delivery;

        // an invalid delivery address is the temporary one itself, while the sheet is still open
        if (!$cartId || !$deliveryAddressId || !$this->addressRepository->isValid($deliveryAddressId)) {
            return;
        }

        $temporaryAddressIds = $this->cartRepository->getTemporaryApplePayLineAddressIds($cartId);

        if (empty($temporaryAddressIds)) {
            return;
        }

        foreach ($temporaryAddressIds as $temporaryAddressId) {
            $this->cartRepository->moveLinesToAddress($cartId, $temporaryAddressId, $deliveryAddressId);
        }

        // package and delivery option lists may already be cached for this request with the old line addresses
        Cart::resetStaticCache();
    }
}
