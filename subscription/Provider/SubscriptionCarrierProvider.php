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

namespace Mollie\Subscription\Provider;

use Mollie\Factory\ModuleFactory;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Subscription\DTO\SubscriptionCarrierProviderData;
use Mollie\Subscription\DTO\SubscriptionOrderAmountProviderData;
use Mollie\Subscription\DTO\UpdateSubscriptionData;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierData;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Utility\SecureKeyUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCarrierProvider
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var \Mollie */
    private $module;
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ModuleFactory $moduleFactory,
        SubscriptionProductProvider $subscriptionProductProvider,
        SubscriptionOrderAmountProvider $subscriptionOrderAmountProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->module = $moduleFactory->getModule();
        $this->subscriptionProductProvider = $subscriptionProductProvider;
        $this->subscriptionOrderAmountProvider = $subscriptionOrderAmountProvider;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function get(SubscriptionCarrierProviderData $data): UpdateSubscriptionData
    {
        /** @var ?\Order $order */
        $order = $this->orderRepository->findOneBy([
            'id_order' => $data->getOrderId(),
        ]);

        if (!$order) {
            throw CouldNotProvideSubscriptionCarrierData::failedToFindOrder($data->getOrderId());
        }

        $subscriptionProduct = $this->subscriptionProductProvider->getProduct($order->getCartProducts());

        if (empty($subscriptionProduct)) {
            throw CouldNotProvideSubscriptionCarrierData::failedToFindSubscriptionProduct();
        }

        $key = SecureKeyUtility::generateReturnKey(
            (int) $order->id_customer,
            (int) $order->id_cart,
            $this->module->name
        );

        $metadata = [
            'secure_key' => $key,
            'subscription_carrier_id' => $data->getSubscriptionCarrierId(),
        ];

        try {
            $orderAmount = $this->subscriptionOrderAmountProvider->get(
                SubscriptionOrderAmountProviderData::create(
                    (int) $order->id_address_delivery,
                    (int) $order->id_cart,
                    (int) $order->id_customer,
                    $subscriptionProduct,
                    $data->getSubscriptionCarrierId(),
                    (int) $order->id_currency
                )
            );
        } catch (\Throwable $exception) {
            throw CouldNotProvideSubscriptionCarrierData::failedToProvideSubscriptionOrderAmount($exception);
        }

        return new UpdateSubscriptionData(
            $data->getMollieCustomerId(),
            $data->getMollieSubscriptionId(),
            null,
            $metadata,
            $orderAmount
        );
    }
}
