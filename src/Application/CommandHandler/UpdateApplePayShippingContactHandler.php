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

namespace Mollie\Application\CommandHandler;

use Address;
use Carrier;
use Cart;
use Configuration;
use Country;
use Customer;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\Config\Config;
use Mollie\Exception\GuestCheckoutNotAvailableException;
use Mollie\Service\OrderPaymentFeeService;
use Mollie\Utility\ApplePayDirect\ShippingMethodUtility;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class UpdateApplePayShippingContactHandler
{
    /**
     * @var ApplePayCarriersBuilder
     */
    private $applePayCarriersBuilder;

    /**
     * @var OrderPaymentFeeService
     */
    private $orderPaymentFeeService;
    /** @var OrderTotalCollector */
    private $orderTotalCollector;

    public function __construct(
        ApplePayCarriersBuilder $applePayCarriersBuilder,
        OrderPaymentFeeService $orderPaymentFeeService,
        OrderTotalCollector $orderTotalCollector
    ) {
        $this->applePayCarriersBuilder = $applePayCarriersBuilder;
        $this->orderPaymentFeeService = $orderPaymentFeeService;
        $this->orderTotalCollector = $orderTotalCollector;
    }

    public function handle(UpdateApplePayShippingContact $command): array
    {
        $cart = new Cart($command->getCartId());
        $customer = $this->getOrCreateCustomer($command->getCustomerId(), $cart);
        $deliveryAddress = $this->getOrCreateAddress($cart->id_address_delivery, $customer->id, $command);
        $invoiceAddress = $this->getOrCreateAddress($cart->id_address_invoice, $customer->id, $command);
        $this->updateCart($cart, $customer, $deliveryAddress->id, $invoiceAddress->id);
        $this->addProductToCart($cart, $command);
        $cart = new Cart($cart->id);
        $this->updateContext($cart, $customer);
        $country = new Country($deliveryAddress->id_country);

        $applePayCarriers = $this->applePayCarriersBuilder->build(Carrier::getCarriersForOrder($country->id_zone), $country->id_zone);

        $shippingMethods = ShippingMethodUtility::collectShippingMethodData($applePayCarriers, $cart);
        $totals = $this->orderTotalCollector->getOrderTotals($applePayCarriers, $cart);

        $paymentFee = 0;

        if ($totals) {
            $paymentFeeData = $this->orderPaymentFeeService->getPaymentFee($totals[0]['amountWithoutFee'], Config::APPLEPAY);

            $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();
        }

        return [
            'data' => [
                'shipping_methods' => $shippingMethods,
                'totals' => $totals,
                'paymentFee' => [
                    'label' => 'Payment fee',
                    'amount' => $paymentFee,
                    'type' => 'final',
                ],
            ],
            'success' => true,
        ];
    }

    private function getOrCreateAddress(int $existingAddressId, int $customerId, UpdateApplePayShippingContact $command): Address
    {
        if ($existingAddressId) {
            $address = new Address($existingAddressId);
            if ($address->id && $address->alias === 'applePay') {
                $address->postcode = $command->getPostalCode();
                $address->id_country = Country::getByIso($command->getCountryCode());
                $address->country = $command->getCountry();
                $address->city = $command->getLocality();
                $address->id_customer = $customerId;
                $address->update();

                return $address;
            }
        }

        $address = new Address();
        $address->address1 = 'ApplePay';
        $address->lastname = 'ApplePay';
        $address->firstname = 'ApplePay';
        $address->id_customer = $customerId;
        $address->alias = 'applePay';
        $address->postcode = $command->getPostalCode();
        $address->id_country = Country::getByIso($command->getCountryCode());
        $address->country = $command->getCountry();
        $address->city = $command->getLocality();
        $address->add();

        return $address;
    }

    private function getOrCreateCustomer(int $customerId, Cart $cart): Customer
    {
        if ($customerId) {
            return new Customer($customerId);
        }

        if ($cart->id_customer) {
            return new Customer($cart->id_customer);
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
            throw GuestCheckoutNotAvailableException::guestCheckoutDisabled();
        }

        $customer = new Customer();
        $customer->is_guest = true;
        $customer->firstname = 'applePay';
        $customer->lastname = 'applePay';
        $customer->email = 'applepay-' . (int) $cart->id . '@mollie.com';
        $customer->passwd = Tools::hash(microtime());
        $customer->add();

        return $customer;
    }

    private function updateCart(Cart $cart, Customer $customer, int $deliveryAddressId, int $invoiceAddressId): void
    {
        $cart->secure_key = $customer->secure_key;
        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->id_customer = $customer->id;
        $cart->update();
    }

    private function addProductToCart(Cart $cart, UpdateApplePayShippingContact $command)
    {
        foreach ($command->getProducts() as $product) {
            $cart->deleteProduct($product->getProductId(), $product->getProductAttribute());
            $quantity = max($product->getWantedQuantity(), 1);
            $cart->updateQty($quantity, $product->getProductId(), $product->getProductAttribute());
        }
    }

    private function updateContext(Cart $cart, Customer $customer)
    {
        $context = \Context::getContext();
        $context->cart = $cart;
        \Context::getContext()->updateCustomer($customer);
    }
}
