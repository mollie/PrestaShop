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
use Mollie\Exception\MollieException;
use Mollie\Exception\TransactionException;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\CloneOriginalSubscriptionCartData;
use Mollie\Subscription\Exception\CouldNotHandleRecurringOrder;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\SecureKeyUtility;
use MolRecurringOrder;
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
    /** @var CarrierRepositoryInterface */
    private $carrierRepository;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var CloneOriginalSubscriptionCartHandler */
    private $cloneOriginalSubscriptionCartHandler;

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
        CarrierRepositoryInterface $carrierRepository,
        // TODO use subscription logger after it's fixed
        PrestaLoggerInterface $logger,
        CloneOriginalSubscriptionCartHandler $cloneOriginalSubscriptionCartHandler
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
        $this->carrierRepository = $carrierRepository;
        $this->logger = $logger;
        $this->cloneOriginalSubscriptionCartHandler = $cloneOriginalSubscriptionCartHandler;
    }

    /**
     * @throws \Throwable
     */
    public function handle(string $transactionId): string
    {
        $transaction = $this->mollie->getApiClient()->payments->get($transactionId);

        try {
            /** @var \MolRecurringOrder $recurringOrder */
            $recurringOrder = $this->recurringOrderRepository->findOrFail([
                'mollie_subscription_id' => $transaction->subscriptionId,
            ]);
        } catch (\Throwable $exception) {
            throw TransactionException::unknownError($exception);
        }

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
        try {
            $newCart = $this->cloneOriginalSubscriptionCartHandler->run(
                CloneOriginalSubscriptionCartData::create(
                    (int) $recurringOrder->id_cart,
                    (int) $recurringOrder->id_mol_recurring_orders_product,
                    (int) $recurringOrder->id_address_invoice,
                    (int) $recurringOrder->id_address_delivery
                )
            );
        } catch (\Throwable $exception) {
            throw CouldNotHandleRecurringOrder::failedToHandleSubscriptionCartCloning($exception);
        }

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

        $updatedCartCarrierId = (int) ($newCart->getDeliveryOption(null, false, false)[$newCart->id_address_delivery] ?? 0);

        if ((int) $carrier->id !== $updatedCartCarrierId) {
            throw CouldNotHandleRecurringOrder::failedToApplySelectedCarrier((int) $carrier->id, $updatedCartCarrierId);
        }

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
            throw $exception;
        }

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

    /**
     * @throws \Throwable
     */
    private function cancelSubscription(int $recurringOrderId): void
    {
        try {
            /** @var \MolRecurringOrder $recurringOrder */
            $recurringOrder = $this->recurringOrderRepository->findOrFail([
                'id_mol_recurring_order' => $recurringOrderId,
            ]);
        } catch (\Throwable $exception) {
            throw MollieException::unknownError($exception);
        }

        $recurringOrder->status = SubscriptionStatus::STATUS_CANCELED;
        $recurringOrder->cancelled_at = $this->clock->getCurrentDate();
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->update();

        $this->mailService->sendSubscriptionCancelWarningEmail($recurringOrderId);
    }
}
