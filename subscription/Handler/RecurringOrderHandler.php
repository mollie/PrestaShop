<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Action\CreateOrderPaymentFeeAction;
use Mollie\Action\UpdateOrderTotalsAction;
use Mollie\Adapter\Shop;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\DTO\UpdateOrderTotalsData;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\CouldNotCreateOrderPaymentFee;
use Mollie\Exception\CouldNotUpdateOrderTotals;
use Mollie\Exception\OrderCreationException;
use Mollie\Exception\TransactionException;
use Mollie\Repository\MolOrderPaymentFeeRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Exception\CouldNotHandleRecurringOrder;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\SecureKeyUtility;
use MolOrderPaymentFee;
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
    /** @var MolOrderPaymentFeeRepositoryInterface */
    private $molOrderPaymentFeeRepository;
    /** @var UpdateOrderTotalsAction */
    private $updateOrderTotalsAction;
    /** @var CreateOrderPaymentFeeAction */
    private $createOrderPaymentFeeAction;

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
        MolOrderPaymentFeeRepositoryInterface $molOrderPaymentFeeRepository,
        UpdateOrderTotalsAction $updateOrderTotalsAction,
        CreateOrderPaymentFeeAction $createOrderPaymentFeeAction
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
        $this->molOrderPaymentFeeRepository = $molOrderPaymentFeeRepository;
        $this->updateOrderTotalsAction = $updateOrderTotalsAction;
        $this->createOrderPaymentFeeAction = $createOrderPaymentFeeAction;
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
     * @throws CouldNotHandleRecurringOrder
     * @throws OrderCreationException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createSubscription(Payment $transaction, MolRecurringOrder $recurringOrder, MollieSubscription $subscription): void
    {
        $cart = new Cart($recurringOrder->id_cart);

        /** @var $newCart array{success: bool, cart: Cart}|bool $newCart */
        $newCart = $cart->duplicate();

        if (!$newCart || !$newCart['success']) {
            return;
        }

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);

        $methodName = $paymentMethod->method_name ?: Config::$methods[$transaction->method];

        /** @var Cart $newCart */
        $newCart = $newCart['cart'];

        /**
         * NOTE: New order can't have soft deleted delivery address
         */
        $newCart = $this->updateSubscriptionOrderAddress(
            $newCart,
            (int) $recurringOrder->id_address_invoice,
            (int) $recurringOrder->id_address_delivery
        );

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

        $orderId = (int) Order::getIdByCartId((int) $newCart->id);
        $order = new Order($orderId);

        $specificPrice->delete();

        $this->mollieOrderCreationService->createMolliePayment($transaction, (int) $newCart->id, $order->reference, (int) $orderId, PaymentStatus::STATUS_PAID);

        /** @var MolOrderPaymentFee|null $molOrderPaymentFee */
        $molOrderPaymentFee = $this->molOrderPaymentFeeRepository->findOneBy([
            'id_order' => $recurringOrder->id_order,
        ]);

        if ($molOrderPaymentFee) {
            try {
                $this->createOrderPaymentFeeAction->run(CreateOrderPaymentFeeActionData::create(
                    $orderId,
                    (int) $newCart->id,
                    (float) $molOrderPaymentFee->fee_tax_incl,
                    (float) $molOrderPaymentFee->fee_tax_excl
                ));
            } catch (CouldNotCreateOrderPaymentFee $exception) {
                throw CouldNotHandleRecurringOrder::failedToCreateOrderPaymentFee($exception);
            }

            try {
                $this->updateOrderTotalsAction->run(UpdateOrderTotalsData::create(
                    $orderId,
                    (float) $molOrderPaymentFee->fee_tax_incl,
                    (float) $molOrderPaymentFee->fee_tax_excl,
                    (float) $transaction->amount->value,
                    (float) $cart->getOrderTotal(true, Cart::BOTH),
                    (float) $cart->getOrderTotal(false, Cart::BOTH)
                ));
            } catch (CouldNotUpdateOrderTotals $exception) {
                throw CouldNotHandleRecurringOrder::failedToUpdateOrderTotalWithPaymentFee($exception);
            }
        }
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
