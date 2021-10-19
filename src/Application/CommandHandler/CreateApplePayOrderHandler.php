<?php

namespace Mollie\Application\CommandHandler;

use Address;
use Cart;
use Configuration;
use Country;
use Currency;
use Link;
use Mollie;
use Mollie\Application\Command\CreateApplePayOrder;
use Mollie\Config\Config;
use Mollie\DTO\ApplePay\ShippingContent;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\PaymentMethodService;
use Mollie\Utility\OrderNumberUtility;
use MolPaymentMethod;
use Order;
use Tools;

final class CreateApplePayOrderHandler
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;
    /**
     * @var MollieOrderCreationService
     */
    private $mollieOrderCreationService;
    /**
     * @var Link
     */
    private $link;
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        MollieOrderCreationService $mollieOrderCreationService,
        Link $link,
        Mollie $module
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
        $this->link = $link;
        $this->module = $module;
    }

    public function handle(CreateApplePayOrder $command): array
    {
        $cart = new Cart($command->getCartId());
        $this->updateCardInfo($cart->id_address_delivery, $command->getOrder()->getShippingContent());
        $this->updateCardInfo($cart->id_address_invoice, $command->getOrder()->getBillingContent());
        $this->updateCustomer($cart->id_customer, $command->getOrder()->getShippingContent());

        try {
            $apiPayment = $this->createMollieTransaction($cart, $command->getCardToken());
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'STATUS_FAILURE',
                'errors' => [
                    [
                        'code' => 'unknown',
                        'contactField' => null,
                        'message' => $this->module->l('Failed to create mollie transaction.'),
                    ],
                ],
            ];
        }

        // we need to wait for webhook to create the order. That's why we wait here for few seconds and check if order is created
        $tries = 0;
        while ($tries <= 10) {
            $order = Order::getByCartId($command->getCartId());
            if ($order) {
                break;
            }
            ++$tries;
            sleep(1);
        }

        if (!$order) {
            return [
                'success' => false,
                'status' => 'STATUS_FAILURE',
                'errors' => [
                    [
                        'code' => 'unknown',
                        'contactField' => null,
                        'message' => $this->module->l('Couldn\'t find order by card it.'),
                    ],
                ],
            ];
        }

        $this->deleteAddress($order->id_address_delivery);
        $this->deleteAddress($order->id_address_invoice);

        $this->mollieOrderCreationService->createOrder($apiPayment, $cart->id, $order->reference);

        $successUrl = $this->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => Order::getByCartId($cart->id),
                'key' => $cart->secure_key,
            ]
        );

        return [
            'success' => true,
            'successUrl' => $successUrl,
            'responseToApple' => ['status' => 0],
        ];
    }

    private function updateCardInfo(int $addressId, ShippingContent $shippingContent)
    {
        $address = new Address($addressId);
        $address->firstname = $shippingContent->getGivenName();
        $address->lastname = $shippingContent->getFamilyName();
        $address->city = $shippingContent->getLocality();
        $address->country = $shippingContent->getCountry();
        $address->id_country = Country::getByIso($shippingContent->getCountryCode());
        $address->postcode = $shippingContent->getPostalCode();
        if (isset($shippingContent->getAddressLines()[0])) {
            $address->address1 = $shippingContent->getAddressLines()[0];
        }
        if (isset($shippingContent->getAddressLines()[1])) {
            $address->address2 = $shippingContent->getAddressLines()[1];
        }

        $address->update();
    }

    private function updateCustomer(int $customerId, ShippingContent $shippingContent)
    {
        $customer = new \Customer($customerId);
        $customer->firstname = $shippingContent->getGivenName();
        $customer->lastname = $shippingContent->getFamilyName();
        $customer->email = $shippingContent->getEmailAddress();

        $customer->update();
    }

    private function createMollieTransaction(Cart $cart, string $cardToken)
    {
        $currency = new Currency($cart->id_currency);
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId(Config::APPLEPAY, Configuration::get(Config::MOLLIE_ENVIRONMENT));

        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);

        $paymentData = $this->paymentMethodService->getPaymentData(
            $cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier),
            Tools::strtoupper($currency->iso_code),
            Config::APPLEPAY,
            null,
            (int) $cart->id,
            $cart->secure_key,
            $paymentMethodObj,
            OrderNumberUtility::generateOrderNumber($cart->id),
            '',
            false,
            false,
            $cardToken
        );

        return $this->mollieOrderCreationService->createMollieApplePayDirectOrder($paymentData, $paymentMethodObj);
    }

    private function deleteAddress(int $addressId): void
    {
        $address = new Address($addressId);
        $address->deleted = 1;
        $address->update();
    }
}