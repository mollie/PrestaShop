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
use Mollie\DTO\ApplePay\Product;
use Mollie\Service\ApplePayCartProductsService;
use PHPUnit\Framework\TestCase;

/**
 * The Apple Pay Direct sheet loads the wanted products into the cart it quotes. A customized
 * line is keyed by its customization row, so it has to be matched on that row, otherwise the
 * sheet adds a plain copy of the product next to it.
 */
class ApplePayCartProductsServiceTest extends TestCase
{
    const PRODUCT_ID = 19;
    const CUSTOMIZATION_ID = 4;

    public function testItLeavesACustomizedLineThatIsAlreadyInTheCartAlone()
    {
        $cart = $this->createMock(Cart::class);
        $cart->method('getProductQuantity')->willReturnMap([
            [self::PRODUCT_ID, 0, self::CUSTOMIZATION_ID, 0, ['quantity' => 2]],
            [self::PRODUCT_ID, 0, 0, 0, ['quantity' => 0]],
        ]);

        $cart->expects($this->never())->method('deleteProduct');
        $cart->expects($this->never())->method('updateQty');

        (new ApplePayCartProductsService())->addWantedProducts($cart, [new Product(self::PRODUCT_ID, 0, 2, self::CUSTOMIZATION_ID)]);
    }

    public function testItLeavesAPlainLineWithTheWantedQuantityAlone()
    {
        $cart = $this->createMock(Cart::class);
        $cart->method('getProductQuantity')->with(self::PRODUCT_ID, 3, 0)->willReturn(['quantity' => 2]);

        $cart->expects($this->never())->method('deleteProduct');
        $cart->expects($this->never())->method('updateQty');

        (new ApplePayCartProductsService())->addWantedProducts($cart, [new Product(self::PRODUCT_ID, 3, 2)]);
    }

    public function testItReplacesAPlainLineWithAnotherQuantity()
    {
        $cart = $this->createMock(Cart::class);
        $cart->method('getProductQuantity')->with(self::PRODUCT_ID, 3, 0)->willReturn(['quantity' => 1]);

        $cart->expects($this->once())->method('deleteProduct')->with(self::PRODUCT_ID, 3);
        $cart->expects($this->once())->method('updateQty')->with(2, self::PRODUCT_ID, 3);

        (new ApplePayCartProductsService())->addWantedProducts($cart, [new Product(self::PRODUCT_ID, 3, 2)]);
    }

    /**
     * The product page sheet quotes a fresh cart that does not own the session cart's
     * customization row, so the product is added plain, as before.
     */
    public function testItAddsTheProductPlainWhenTheCartDoesNotHoldTheCustomization()
    {
        $cart = $this->createMock(Cart::class);
        $cart->method('getProductQuantity')->willReturn(['quantity' => 0]);

        $cart->expects($this->once())->method('deleteProduct')->with(self::PRODUCT_ID, 0);
        $cart->expects($this->once())->method('updateQty')->with(1, self::PRODUCT_ID, 0);

        (new ApplePayCartProductsService())->addWantedProducts($cart, [new Product(self::PRODUCT_ID, 0, 1, self::CUSTOMIZATION_ID)]);
    }
}
