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

use Cart;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Service\ApplePayCartLinesAddressService;
use PHPUnit\Framework\TestCase;

/**
 * GH-1594: a cancelled Apple Pay Direct sheet leaves the cart lines on the soft-deleted
 * temporary address. Once the cart gets a real delivery address the lines must follow it,
 * otherwise checkout finds no carrier for the cart.
 */
class ApplePayCartLinesAddressServiceTest extends TestCase
{
    const CART_ID = 7;
    const REAL_ADDRESS_ID = 2;
    const TEMPORARY_ADDRESS_ID = 9;

    /** @var CartRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cartRepository;

    /** @var AddressRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $addressRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
    }

    public function testItMovesLinesFromTheTemporaryAddressToTheCartDeliveryAddress()
    {
        $this->addressRepository->method('isValid')->with(self::REAL_ADDRESS_ID)->willReturn(true);
        $this->cartRepository->method('getTemporaryApplePayLineAddressIds')
            ->with(self::CART_ID)
            ->willReturn([self::TEMPORARY_ADDRESS_ID]);

        $this->cartRepository->expects($this->once())
            ->method('moveLinesToAddress')
            ->with(self::CART_ID, self::TEMPORARY_ADDRESS_ID, self::REAL_ADDRESS_ID);

        $this->createService()->moveLinesOffTemporaryAddresses($this->createCart(self::REAL_ADDRESS_ID));
    }

    /**
     * While the sheet is open the cart itself points at the temporary address, and the
     * lines must stay there so the sheet is quoted against the shopper's contact.
     */
    public function testItLeavesLinesAloneWhileTheCartSitsOnTheTemporaryAddress()
    {
        $this->addressRepository->method('isValid')->with(self::TEMPORARY_ADDRESS_ID)->willReturn(false);
        $this->cartRepository->method('getTemporaryApplePayLineAddressIds')->willReturn([self::TEMPORARY_ADDRESS_ID]);

        $this->cartRepository->expects($this->never())->method('moveLinesToAddress');

        $this->createService()->moveLinesOffTemporaryAddresses($this->createCart(self::TEMPORARY_ADDRESS_ID));
    }

    /**
     * Core wipes the soft-deleted address off the cart on the next request. There is no
     * address to move the lines to yet; core moves lines on address 0 once one is chosen.
     */
    public function testItLeavesLinesAloneWhileTheCartHasNoDeliveryAddress()
    {
        $this->addressRepository->expects($this->never())->method('isValid');
        $this->cartRepository->expects($this->never())->method('moveLinesToAddress');

        $this->createService()->moveLinesOffTemporaryAddresses($this->createCart(0));
    }

    /**
     * The hook runs on every cart save in the shop, so a cart that never saw the sheet
     * costs one query and no address lookup.
     */
    public function testItDoesNothingWhenNoLineSitsOnATemporaryAddress()
    {
        $this->cartRepository->method('getTemporaryApplePayLineAddressIds')->willReturn([]);

        $this->addressRepository->expects($this->never())->method('isValid');
        $this->cartRepository->expects($this->never())->method('moveLinesToAddress');

        $this->createService()->moveLinesOffTemporaryAddresses($this->createCart(self::REAL_ADDRESS_ID));
    }

    public function testItIgnoresACartThatIsNotSavedYet()
    {
        $cart = $this->createCart(self::REAL_ADDRESS_ID);
        $cart->id = null;

        $this->addressRepository->expects($this->never())->method('isValid');
        $this->cartRepository->expects($this->never())->method('moveLinesToAddress');

        $this->createService()->moveLinesOffTemporaryAddresses($cart);
    }

    private function createService(): ApplePayCartLinesAddressService
    {
        return new ApplePayCartLinesAddressService($this->cartRepository, $this->addressRepository);
    }

    private function createCart(int $deliveryAddressId): Cart
    {
        $cart = $this->createMock(Cart::class);
        $cart->id = self::CART_ID;
        $cart->id_address_delivery = $deliveryAddressId;

        return $cart;
    }
}
