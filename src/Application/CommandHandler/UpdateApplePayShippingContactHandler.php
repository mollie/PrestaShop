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
use Country;
use Customer;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\Config\Config;
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
        $customer = $this->createCustomer($command->getCustomerId());
        $deliveryAddress = $this->createAddress($customer->id, $command);
        $invoiceAddress = $this->createAddress($customer->id, $command);
        $cart = $this->updateCart($customer, $deliveryAddress->id, $invoiceAddress->id, $command->getCartId());
        $this->addProductToCart($cart, $command);

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

    private function createAddress(int $customerId, UpdateApplePayShippingContact $command): Address
    {
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

    private function createCustomer(int $customerId): Customer
    {
        if ($customerId) {
            return new Customer($customerId);
        }
        $customer = new Customer();
        $customer->is_guest = true;
        $customer->firstname = 'applePay';
        $customer->lastname = 'applePay';
        $customer->email = 'applePay@mollie.com';
        $customer->passwd = Tools::hash(microtime());
        $customer->add();

        return $customer;
    }

    private function updateCart(Customer $customer, int $deliveryAddressId, int $invoiceAddressId, int $cartId): cart
    {
        $cart = new Cart($cartId);
        $cart->secure_key = $customer->secure_key;
        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->id_customer = $customer->id;
        $cart->update();

        return $cart;
    }

    private function addProductToCart(Cart $cart, UpdateApplePayShippingContact $command)
    {
        foreach ($command->getProducts() as $product) {
            $cart->deleteProduct($product->getProductId(), $product->getProductAttribute());
            $cart->updateQty($product->getWantedQuantity(), $product->getProductId(), $product->getProductAttribute());
        }
    }
}
