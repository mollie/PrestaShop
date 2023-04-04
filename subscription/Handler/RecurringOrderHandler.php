<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Adapter\Shop;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Service\TransactionService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
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
    /** @var TransactionService */
    private $transactionService;

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
        MailService $mailService
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

    private function createSubscription(Payment $transaction, MolRecurringOrder $recurringOrder, MollieSubscription $subscription)
    {
        $cart = new Cart($recurringOrder->id_cart);
        $newCart = $cart->duplicate();
        if (!$newCart['success']) {
            return;
        }

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);

        $methodName = $paymentMethod->method_name ?: Config::$methods[$transaction->method];

        /** @var Cart $newCart */
        $newCart = $newCart['cart'];

        $newCart->id_address_invoice = $recurringOrder->id_address_invoice;
        $newCart->id_address_shipping = $recurringOrder->id_address_delivery;

        $recurringOrderProduct = new MolRecurringOrdersProduct($recurringOrder->id_mol_recurring_orders_product);

        $specificPrice = $this->createSpecificPrice($recurringOrderProduct, $recurringOrder);

        $this->mollie->validateOrder(
            (int) $newCart->id,
            Config::getStatuses()[$transaction->status],
            (float) $subscription->amount->value,
            sprintf('subscription/%s', $methodName),
            null,
            ['transaction_id' => $transaction->id],
            null,
            false,
            $newCart->secure_key
        );

        $orderId = Order::getIdByCartId((int) $newCart->id);
        $order = new Order($orderId);

        $specificPrice->delete();

        $this->mollieOrderCreationService->createMolliePayment($transaction, (int) $newCart->id, $order->reference, (int) $orderId, PaymentStatus::STATUS_PAID);
    }

    private function updateOrderStatus(Payment $transaction, int $orderId)
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
}
