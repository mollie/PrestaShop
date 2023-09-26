<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Shop;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Exception\CouldNotHandleRecurringOrder;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\SecureKeyUtility;
use MolRecurringOrder;
use MolRecurringOrdersProduct;
use Order;
use SpecificPrice;

class RecurringOrderHandler
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var GetSubscriptionDataFactory */
    private $subscriptionDataFactory;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var Mollie */
    private $mollie;
    /** @var MollieOrderCreationService */
    private $mollieOrderCreationService;
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;
    /** @var OrderStatusService */
    private $orderStatusService;
    /** @var PaymentMethodService */
    private $paymentMethodService;
    /** @var ClockInterface */
    private $clock;
    /** @var Shop */
    private $shop;
    /** @var MailService */
    private $mailService;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var RecurringOrdersProductRepositoryInterface */
    private $recurringOrdersProductRepository;
    /** @var CarrierRepositoryInterface */
    private $carrierRepository;
    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        GetSubscriptionDataFactory $subscriptionDataFactory,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Mollie $mollie,
        MollieOrderCreationService $mollieOrderCreationService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderStatusService $orderStatusService,
        PaymentMethodService $paymentMethodService,
        ClockInterface $clock,
        Shop $shop,
        MailService $mailService,
        ConfigurationAdapter $configuration,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        CarrierRepositoryInterface $carrierRepository,
        PrestaLoggerInterface $logger
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->mollie = $mollie;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderStatusService = $orderStatusService;
        $this->paymentMethodService = $paymentMethodService;
        $this->clock = $clock;
        $this->shop = $shop;
        $this->mailService = $mailService;
        $this->configuration = $configuration;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->carrierRepository = $carrierRepository;
        $this->logger = $logger;
    }

    public function handle(string $transactionId): string
    {
        $transaction = $this->mollie->getApiClient()->payments->get($transactionId);
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['mollie_subscription_id' => $transaction->subscriptionId]);
        $subscriptionData = $this->subscriptionDataFactory->build((int) $recurringOrder->id);
        $subscription = $this->subscriptionApi->getSubscription($subscriptionData);

        $key = SecureKeyUtility::generateReturnKey(
            $recurringOrder->id_customer,
            $recurringOrder->id_cart,
            $this->mollie->name
        );

        if ($key !== $subscription->metadata->secure_key) {
            throw new TransactionException('Security key is incorrect.', HttpStatusCode::HTTP_UNAUTHORIZED);
        }

        $existingTransaction = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transaction->id);

        // TODO separate these actions into separate service. Test them as well.
        switch (true) {
            case $existingTransaction:
                $this->updateOrderStatus($transaction, (int) $existingTransaction['order_id']);
                break;
            case $transaction->status === PaymentStatus::STATUS_PAID:
                $this->createSubscription($transaction, $recurringOrder, $subscription);
                break;
            case $transaction->status === PaymentStatus::STATUS_FAILED:
                $this->handleFailedTransaction((int) $recurringOrder->id);
                break;
            default:
                break;
        }

        return 'OK';
    }

    /**
     * @throws \Throwable
     */
    private function createSubscription(Payment $transaction, MolRecurringOrder $recurringOrder, MollieSubscription $subscription): void
    {
        $cart = new Cart($recurringOrder->id_cart);

        /** @var array{success: bool, cart: Cart}|bool $newCart */
        $newCart = $cart->duplicate();

        if (!$newCart || !$newCart['success']) {
            return;
        }

        /** @var Cart $newCart */
        $newCart = $newCart['cart'];

        /** @var MolRecurringOrdersProduct $subscriptionProduct */
        $subscriptionProduct = $this->recurringOrdersProductRepository->findOneBy([
            'id_mol_recurring_orders_product' => $recurringOrder->id_mol_recurring_orders_product,
        ]);

        $cartProducts = $newCart->getProducts();

        foreach ($cartProducts as $cartProduct) {
            if (
                (int) $cartProduct['id_product'] === (int) $subscriptionProduct->id_product &&
                (int) $cartProduct['id_product_attribute'] === (int) $subscriptionProduct->id_product_attribute
            ) {
                continue;
            }

            $newCart->deleteProduct((int) $cartProduct['id_product'], (int) $cartProduct['id_product_attribute']);
        }

        /**
         * NOTE: New order can't have soft deleted delivery address
         */
        $newCart = $this->updateSubscriptionOrderAddress(
            $newCart,
            (int) $recurringOrder->id_address_invoice,
            (int) $recurringOrder->id_address_delivery
        );

        $recurringOrderProduct = new MolRecurringOrdersProduct($recurringOrder->id_mol_recurring_orders_product);

        $subscriptionCarrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        /** @var \Carrier|null $carrier */
        $carrier = $this->carrierRepository->findOneBy([
            'id_carrier' => $subscriptionCarrierId,
            'active' => 1,
            'deleted' => 0,
        ]);

        if (!$carrier) {
            throw CouldNotHandleRecurringOrder::failedToFindSelectedCarrier();
        }

        $newCart->setDeliveryOption(
            [(int) $newCart->id_address_delivery => sprintf('%d,', (int) $carrier->id)]
        );

        $newCart->update();

        if (sprintf('%d,', (int) $carrier->id) !==
            $newCart->getDeliveryOption(null, false, false)[$newCart->id_address_delivery]
        ) {
            throw CouldNotHandleRecurringOrder::failedToApplySelectedCarrier();
        }

        $specificPrice = $this->createSpecificPrice($recurringOrderProduct, $recurringOrder);

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);

        $methodName = $paymentMethod->method_name ?: Config::$methods[$transaction->method];

        try {
            $this->mollie->validateOrder(
                (int) $newCart->id,
                (int) $this->configuration->get(Config::MOLLIE_STATUS_AWAITING),
                (float) $subscription->amount->value,
                sprintf('subscription/%s', $methodName),
                null,
                ['transaction_id' => $transaction->id],
                null,
                false,
                $newCart->secure_key
            );
        } catch (\Throwable $exception) {
            $specificPrice->delete();

            throw $exception;
        }

        $specificPrice->delete();

        $orderId = (int) Order::getIdByCartId((int) $newCart->id);
        $order = new Order($orderId);

        if ((float) $order->total_paid_tax_incl !== (float) $subscription->amount->value) {
            $this->logger->error('Paid price is not equal to the order\'s total', [
                'Paid price' => (float) $subscription->amount->value,
                'Order price' => (float) $order->total_paid_tax_incl,
            ]);
        }

        $this->mollieOrderCreationService->createMolliePayment($transaction, (int) $newCart->id, $order->reference, (int) $orderId, PaymentStatus::STATUS_PAID);

        $this->orderStatusService->setOrderStatus($orderId, (int) Config::getStatuses()[$transaction->status]);
    }

    private function updateOrderStatus(Payment $transaction, int $orderId): void
    {
        $this->orderStatusService->setOrderStatus($orderId, $transaction->status);
        $this->paymentMethodRepository->savePaymentStatus($transaction->id, $transaction->status, $orderId, $transaction->method);
    }

    private function handleFailedTransaction(int $recurringOrderId): void
    {
        $subscriptionData = $this->subscriptionDataFactory->build($recurringOrderId);
        $molSubscription = $this->subscriptionApi->getSubscription($subscriptionData);

        switch ($molSubscription->status) {
            case SubscriptionStatus::STATUS_CANCELED:
            case SubscriptionStatus::STATUS_SUSPENDED:
                $this->cancelSubscription($recurringOrderId);
                break;
            case SubscriptionStatus::STATUS_ACTIVE:
                $this->mailService->sendSubscriptionPaymentFailWarningMail($recurringOrderId);
                break;
        }
    }

    private function cancelSubscription(int $recurringOrderId): void
    {
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['id_mol_recurring_order' => $recurringOrderId]);

        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;
        $recurringOrder->cancelled_at = $this->clock->getCurrentDate();
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();

        $this->mailService->sendSubscriptionCancelWarningEmail($recurringOrderId);
    }

    /**
     * creating temporary specific price for recurring order that will be deleted after order is created
     */
    private function createSpecificPrice(MolRecurringOrdersProduct $molRecurringOrdersProduct, MolRecurringOrder $recurringOrder): SpecificPrice
    {
        $specificPrice = new SpecificPrice();
        $specificPrice->id_product = $molRecurringOrdersProduct->id_product;
        $specificPrice->id_product_attribute = $molRecurringOrdersProduct->id_product_attribute;
        $specificPrice->price = $molRecurringOrdersProduct->unit_price;
        $specificPrice->id_customer = $recurringOrder->id_customer;
        $specificPrice->id_shop = $this->shop->getShop()->id;
        $specificPrice->id_currency = $recurringOrder->id_currency;
        $specificPrice->id_country = 0;
        $specificPrice->id_shop_group = 0;
        $specificPrice->id_group = 0;
        $specificPrice->from_quantity = 0;
        $specificPrice->reduction = 0;
        $specificPrice->reduction_type = 'amount';
        $specificPrice->from = '0000-00-00 00:00:00';
        $specificPrice->to = '0000-00-00 00:00:00';

        $specificPrice->add();

        return $specificPrice;
    }

    private function updateSubscriptionOrderAddress(Cart $cart, int $addressInvoiceId, int $addressDeliveryId): Cart
    {
        $cart->id_address_invoice = $addressInvoiceId;
        $cart->id_address_delivery = $addressDeliveryId;

        $cartProducts = $cart->getProducts();

        foreach ($cartProducts as $cartProduct) {
            $cart->setProductAddressDelivery(
                (int) $cartProduct['id_product'],
                (int) $cartProduct['id_product_attribute'],
                (int) $cartProduct['id_address_delivery'],
                $addressDeliveryId
            );
        }

        $cart->save();

        return $cart;
    }
}
