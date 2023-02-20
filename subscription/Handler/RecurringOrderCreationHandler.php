<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use CartRule;
use Mollie;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\MultiLangUtility;
use Mollie\Utility\SecureKeyUtility;
use MolRecurringOrder;
use MolRecurringOrdersProduct;
use Order;

class RecurringOrderCreationHandler
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

    public function __construct(
        SubscriptionApi $subscriptionApi,
        GetSubscriptionDataFactory $subscriptionDataFactory,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Mollie $mollie,
        MollieOrderCreationService $mollieOrderCreationService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderStatusService $orderStatusService,
        PaymentMethodService $paymentMethodService,
        ClockInterface $clock
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
        $this->createSubscription($transaction, $recurringOrder, $subscription);

        switch (true) {
            case $existingTransaction:
                $this->updateOrderStatus($transaction, (int) $existingTransaction['order_id']);
                break;
            case $transaction->status === PaymentStatus::STATUS_PAID:
                $this->createSubscription($transaction, $recurringOrder, $subscription);
                break;
            case $transaction->status === PaymentStatus::STATUS_FAILED:
                $this->cancelSubscription($recurringOrder->id);
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

        $recurringOrderProduct = new MolRecurringOrdersProduct($recurringOrder->id_mol_recurring_orders_product);
        $newCartRuleId = $this->updateCartPriceRules($newCart, $recurringOrderProduct);

        if ($newCartRuleId) {
            $newCart->addCartRule($newCartRuleId);
        }

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

        if ($newCartRuleId) {
            $newCartRule = new CartRule($newCartRuleId);
            $newCartRule->delete();
        }

        $this->mollieOrderCreationService->createMolliePayment($transaction, $newCart->id, $order->reference, $orderId);
    }

    private function updateOrderStatus(Payment $transaction, int $orderId)
    {
        $this->orderStatusService->setOrderStatus($orderId, $transaction->status);
    }

    private function cancelSubscription(int $recurringOrderId): void
    {
        //todo: do we cancel on first failed transaction or after few?
        $recurringOrder = new MolRecurringOrder($recurringOrderId);

        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;
        $recurringOrder->cancelled_at = $this->clock->getCurrentDate();
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();
    }

    private function updateCartPriceRules(Cart $newCart, MolRecurringOrdersProduct $molRecurringOrdersProduct): int
    {
        foreach ($newCart->getProducts() as $product) {
            if ((int) $product['price'] !== (int) $molRecurringOrdersProduct->unit_price) {
                $cartRule = $this->createCartRule($molRecurringOrdersProduct, (int) $newCart->id_customer);
                return (int) $cartRule->id;
            }
        }

        return 0;
    }

    private function createCartRule(MolRecurringOrdersProduct $molRecurringOrdersProduct, int $customerId): CartRule
    {
        $cartRule = new CartRule();
        $cartRule->id_customer = $customerId;
        $cartRule->name = MultiLangUtility::createMultiLangField('mollie subscription rule');
        $cartRule->reduction_product = $molRecurringOrdersProduct->id_product;
        $cartRule->reduction_amount = $molRecurringOrdersProduct->unit_price;
        $cartRule->reduction_tax = true;
        $cartRule->date_from = $this->clock->getCurrentDate();
        $cartRule->date_to = $this->clock->getDateFromTimeStamp(time() + 24 * 36000);

        $cartRule->add();

        return $cartRule;
    }
}
