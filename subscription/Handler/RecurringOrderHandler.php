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

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Action\CreateSpecificPriceAction;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\CreateSpecificPriceData;
use Mollie\Subscription\Exception\CouldNotHandleRecurringOrder;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\SecureKeyUtility;
use MolRecurringOrder;
use MolRecurringOrdersProduct;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
    /** @var CreateSpecificPriceAction */
    private $createSpecificPriceAction;
    /** @var Mollie\Repository\OrderRepositoryInterface */
    private $orderRepository;

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
        MailService $mailService,
        ConfigurationAdapter $configuration,
        RecurringOrdersProductRepositoryInterface $recurringOrdersProductRepository,
        CarrierRepositoryInterface $carrierRepository,
        // TODO use subscription logger after it's fixed
        PrestaLoggerInterface $logger,
        CreateSpecificPriceAction $createSpecificPriceAction,
        OrderRepositoryInterface $orderRepository
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
        $this->mailService = $mailService;
        $this->configuration = $configuration;
        $this->recurringOrdersProductRepository = $recurringOrdersProductRepository;
        $this->carrierRepository = $carrierRepository;
        $this->logger = $logger;
        $this->createSpecificPriceAction = $createSpecificPriceAction;
        $this->orderRepository = $orderRepository;
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

        /** @var \Order|null $originalOrder */
        $originalOrder = $this->orderRepository->findOneBy([
            'id_order' => $recurringOrder->id_order,
        ]);

        if (!$originalOrder) {
            return;
        }

        /** @var Cart $newCart */
        $newCart = $newCart['cart'];

        $newCart->id_shop = $originalOrder->id_shop;
        $newCart->id_shop_group = $originalOrder->id_shop_group;

        $newCart->update();

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

        $activeSubscriptionCarrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);
        $orderSubscriptionCarrierId = (int) ($subscription->metadata->subscription_carrier_id ?? 0);

        if ($activeSubscriptionCarrierId !== $orderSubscriptionCarrierId) {
            throw CouldNotHandleRecurringOrder::failedToMatchSelectedCarrier($activeSubscriptionCarrierId, $orderSubscriptionCarrierId);
        }

        /** @var \Carrier|null $carrier */
        $carrier = $this->carrierRepository->findOneBy([
            'id_carrier' => $activeSubscriptionCarrierId,
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

        $cartCarrier = (int) ($newCart->getDeliveryOption(null, false, false)[$newCart->id_address_delivery] ?? 0);

        if ((int) $carrier->id !== $cartCarrier) {
            throw CouldNotHandleRecurringOrder::failedToApplySelectedCarrier();
        }

        /**
         * Creating temporary specific price for recurring order that will be deleted after order is created
         */
        $specificPrice = $this->createSpecificPriceAction->run(CreateSpecificPriceData::create(
            (int) $recurringOrderProduct->id_product,
            (int) $recurringOrderProduct->id_product_attribute,
            (float) $recurringOrderProduct->unit_price,
            (int) $recurringOrder->id_customer,
            (int) $newCart->id_shop,
            (int) $newCart->id_shop_group,
            (int) $recurringOrder->id_currency
        ));

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);

        $methodName = $paymentMethod->method_name ?: Config::$methods[$transaction->method];

        $subscriptionPaidTotal = (float) $subscription->amount->value;
        $cartTotal = (float) $newCart->getOrderTotal(true, Cart::BOTH);

        if (!NumberUtility::isEqual($cartTotal, $subscriptionPaidTotal)) {
            // TODO when improved logging with context will be implemented, remove this logging
            $this->logger->error('Paid price is not equal to the order\'s total', [
                'Paid price' => $subscriptionPaidTotal,
                'Order price' => $cartTotal,
            ]);

            throw CouldNotHandleRecurringOrder::cartAndPaidPriceAreNotEqual();
        }

        try {
            $this->mollie->validateOrder(
                (int) $newCart->id,
                (int) Config::getStatuses()[$transaction->status],
                $subscriptionPaidTotal,
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
