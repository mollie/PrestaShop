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

namespace Mollie\Subscription\Handler;

use Mollie\Repository\CartRepositoryInterface;
use Mollie\Subscription\Action\CreateSpecificPriceAction;
use Mollie\Subscription\DTO\CloneOriginalSubscriptionCartData;
use Mollie\Subscription\DTO\CreateSpecificPriceData;
use Mollie\Subscription\Exception\CouldNotHandleOriginalSubscriptionCartCloning;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CloneOriginalSubscriptionCartHandler
{
    /** @var CartRepositoryInterface */
    private $cartRepository;
    /** @var RecurringOrdersProductRepositoryInterface */
    private $recurringOrdersProductRepository;
    /** @var CreateSpecificPriceAction */
    private $createSpecificPriceAction;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        CreateSpecificPriceAction $createSpecificPriceAction
    ) {
        $this->cartRepository = $cartRepository;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->createSpecificPriceAction = $createSpecificPriceAction;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function run(CloneOriginalSubscriptionCartData $data): \Cart
    {
        /** @var ?\Cart $originalCart */
        $originalCart = $this->cartRepository->findOneBy([
            'id_cart' => $data->getCartId(),
        ]);

        if (!$originalCart) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::failedToFindCart($data->getCartId());
        }

        /** @var array{success: bool, cart: \Cart}|bool $duplicatedCart */
        $duplicatedCart = $originalCart->duplicate();

        if (!$duplicatedCart || !$duplicatedCart['success']) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::failedToDuplicateCart($data->getCartId());
        }

        /** @var \Cart $duplicatedCart */
        $duplicatedCart = $duplicatedCart['cart'];

        /** @var ?\MolRecurringOrdersProduct $subscriptionProduct */
        $subscriptionProduct = $this->recurringOrdersProductRepository->findOneBy([
            'id_mol_recurring_orders_product' => $data->getRecurringOrderProductId(),
        ]);

        if (!$subscriptionProduct) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::failedToFindRecurringOrderProduct($data->getRecurringOrderProductId());
        }

        $cartProducts = $duplicatedCart->getProducts();

        foreach ($cartProducts as $cartProduct) {
            if (
                (int) $cartProduct['id_product'] === (int) $subscriptionProduct->id_product &&
                (int) $cartProduct['id_product_attribute'] === (int) $subscriptionProduct->id_product_attribute
            ) {
                continue;
            }

            $duplicatedCart->deleteProduct((int) $cartProduct['id_product'], (int) $cartProduct['id_product_attribute']);
        }

        $cartProducts = $duplicatedCart->getProducts(true);

        if (count($cartProducts) !== 1) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::subscriptionCartShouldHaveOneProduct((int) $duplicatedCart->id);
        }

        /*
         * NOTE: New order can't have soft deleted delivery address
         */
        try {
            $duplicatedCart->id_address_invoice = $data->getInvoiceAddressId();
            $duplicatedCart->id_address_delivery = $data->getDeliveryAddressId();

            $duplicatedCart->setProductAddressDelivery(
                (int) $cartProducts[0]['id_product'],
                (int) $cartProducts[0]['id_product_attribute'],
                (int) $cartProducts[0]['id_address_delivery'],
                $data->getDeliveryAddressId()
            );

            $duplicatedCart->save();
        } catch (\Throwable $exception) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::unknownError($exception);
        }

        /*
         * Creating temporary specific price for recurring order that will be deleted after order is created
         */
        try {
            $specificPrice = $this->createSpecificPriceAction->run(CreateSpecificPriceData::create(
                (int) $subscriptionProduct->id_product,
                (int) $subscriptionProduct->id_product_attribute,
                (float) $subscriptionProduct->unit_price,
                (int) $duplicatedCart->id_customer,
                (int) $duplicatedCart->id_shop,
                (int) $duplicatedCart->id_shop_group,
                (int) $duplicatedCart->id_currency
            ));
        } catch (\Throwable $exception) {
            throw CouldNotHandleOriginalSubscriptionCartCloning::failedToCreateSpecificPrice($exception, (int) $subscriptionProduct->id_product, (int) $subscriptionProduct->id_product_attribute);
        }

        register_shutdown_function([$this, 'onShutdown'], $specificPrice, $duplicatedCart);

        return $duplicatedCart;
    }

    /**
     * On shutdown, we will remove specific price and whole cart object.
     *
     * @throws \Throwable
     */
    public function onShutdown(\SpecificPrice $specificPrice, \Cart $cart): void
    {
        $specificPrice->delete();
        $cart->delete();
    }
}
