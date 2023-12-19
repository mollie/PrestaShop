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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Api\Types\SubscriptionStatus;
use Mollie\Config\Config;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Service\MailService;
use Mollie\Subscription\Action\UpdateRecurringOrderAction;
use Mollie\Subscription\Action\UpdateSubscriptionAction;
use Mollie\Subscription\DTO\CloneOriginalSubscriptionCartData;
use Mollie\Subscription\DTO\SubscriptionOrderAmountProviderData;
use Mollie\Subscription\DTO\UpdateRecurringOrderData;
use Mollie\Subscription\DTO\UpdateSubscriptionData;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionCarrierHandler
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var Context */
    private $context;
    /** @var UpdateSubscriptionAction */
    private $updateSubscriptionAction;
    /** @var UpdateRecurringOrderAction */
    private $updateRecurringOrderAction;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var CloneOriginalSubscriptionCartHandler */
    private $cloneOriginalSubscriptionCartHandler;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;
    /** @var MailService */
    private $mailService;

    public function __construct(
        ConfigurationAdapter $configuration,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Context $context,
        UpdateSubscriptionAction $updateSubscriptionAction,
        UpdateRecurringOrderAction $updateRecurringOrderAction,
        PrestaLoggerInterface $logger,
        CloneOriginalSubscriptionCartHandler $cloneOriginalSubscriptionCartHandler,
        SubscriptionOrderAmountProvider $subscriptionOrderAmountProvider,
        MailService $mailService
    ) {
        $this->configuration = $configuration;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->context = $context;
        $this->updateSubscriptionAction = $updateSubscriptionAction;
        $this->updateRecurringOrderAction = $updateRecurringOrderAction;
        $this->logger = $logger;
        $this->cloneOriginalSubscriptionCartHandler = $cloneOriginalSubscriptionCartHandler;
        $this->subscriptionOrderAmountProvider = $subscriptionOrderAmountProvider;
        $this->mailService = $mailService;
    }

    // TODO feature test this with mocked API request data
    public function run(int $newCarrierId): array
    {
        $activeSubscriptionCarrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        // TODO rethink this. If process failed in any way, maybe merchant would like to repeat it again. We need to track individual orders if they were updated.
        if ($newCarrierId === $activeSubscriptionCarrierId) {
            $this->logger->debug('Same subscription carrier is saved');

            return [];
        }

        $this->configuration->updateValue(
            Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID,
            $newCarrierId
        );

        /** @var array<array{
         *     id: int,
         *     mollie_customer_id: string,
         *     mollie_subscription_id: string,
         *     id_cart: int,
         *     id_recurring_product: int,
         *     id_invoice_address: int,
         *     id_delivery_address: int
         * }> $recurringOrders
         */
        $recurringOrders = $this->recurringOrderRepository->getAllOrdersBasedOnStatuses(
            [
                SubscriptionStatus::STATUS_PENDING,
                SubscriptionStatus::STATUS_ACTIVE,
                SubscriptionStatus::STATUS_SUSPENDED,
            ],
            $this->context->getShopId()
        );

        $failedSubscriptionOrderIdsToUpdate = [];

        foreach ($recurringOrders as $recurringOrder) {
            try {
                $duplicatedCart = $this->cloneOriginalSubscriptionCartHandler->run(
                    CloneOriginalSubscriptionCartData::create(
                        (int) $recurringOrder['id_cart'],
                        (int) $recurringOrder['id_recurring_product'],
                        (int) $recurringOrder['id_invoice_address'],
                        (int) $recurringOrder['id_delivery_address']
                    )
                );
            } catch (\Throwable $exception) {
                $failedSubscriptionOrderIdsToUpdate[] = (string) $recurringOrder['mollie_subscription_id'];

                $this->logger->error('Failed to clone subscription cart.', [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                ]);

                continue;
            }

            $subscriptionProduct = $duplicatedCart->getProducts()[0];

            try {
                $orderAmount = $this->subscriptionOrderAmountProvider->get(
                    SubscriptionOrderAmountProviderData::create(
                        (int) $duplicatedCart->id_address_delivery,
                        (int) $duplicatedCart->id,
                        (int) $duplicatedCart->id_customer,
                        $subscriptionProduct,
                        $newCarrierId,
                        (int) $duplicatedCart->id_currency,
                        (float) $subscriptionProduct['total_price_tax_incl']
                    )
                );
            } catch (\Throwable $exception) {
                $failedSubscriptionOrderIdsToUpdate[] = (string) $recurringOrder['mollie_subscription_id'];

                $this->logger->error('Failed to get subscription order amount.', [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                ]);

                continue;
            }

            try {
                $this->updateSubscriptionAction->run(UpdateSubscriptionData::create(
                    (string) $recurringOrder['mollie_customer_id'],
                    (string) $recurringOrder['mollie_subscription_id'],
                    $orderAmount,
                    (int) $duplicatedCart->id_customer,
                    (int) $duplicatedCart->id,
                    $newCarrierId
                ));
            } catch (\Throwable $exception) {
                $failedSubscriptionOrderIdsToUpdate[] = (string) $recurringOrder['mollie_subscription_id'];

                $this->logger->error('Failed to update subscription.', [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                ]);

                continue;
            }

            try {
                $this->updateRecurringOrderAction->run(UpdateRecurringOrderData::create(
                    (int) $recurringOrder['id'],
                    $orderAmount->getValue()
                ));
            } catch (\Throwable $exception) {
                $failedSubscriptionOrderIdsToUpdate[] = (string) $recurringOrder['mollie_subscription_id'];

                $this->logger->error('Failed to update recurring order record.', [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                ]);

                continue;
            }

            try {
                $this->mailService->sendSubscriptionCarrierUpdateMail((int) $recurringOrder['id']);
            } catch (\Throwable $exception) {
                $failedSubscriptionOrderIdsToUpdate[] = (string) $recurringOrder['mollie_subscription_id'];

                $this->logger->error('Failed to send subscription carrier update mail.', [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                ]);

                continue;
            }
        }

        return $failedSubscriptionOrderIdsToUpdate;
    }
}
