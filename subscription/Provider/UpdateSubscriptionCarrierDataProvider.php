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
use Mollie\Shared\Infrastructure\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\SubscriptionCarrierDeliveryPriceData;
use Mollie\Subscription\DTO\UpdateSubscriptionCarrierData;
use Mollie\Subscription\DTO\UpdateSubscriptionData;
use Mollie\Subscription\Exception\CouldNotProvideUpdateSubscriptionCarrierData;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Utility\SecureKeyUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionCarrierDataProvider
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;
    /** @var \Mollie */
    private $module;
    /** @var SubscriptionCarrierDeliveryPriceProvider */
    private $subscriptionCarrierDeliveryPriceProvider;
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CurrencyRepositoryInterface $currencyRepository,
        ModuleFactory $moduleFactory,
        SubscriptionCarrierDeliveryPriceProvider $subscriptionCarrierDeliveryPriceProvider,
        SubscriptionProductProvider $subscriptionProductProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->currencyRepository = $currencyRepository;
        $this->module = $moduleFactory->getModule();
        $this->subscriptionCarrierDeliveryPriceProvider = $subscriptionCarrierDeliveryPriceProvider;
        $this->subscriptionProductProvider = $subscriptionProductProvider;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function get(UpdateSubscriptionCarrierData $data): UpdateSubscriptionData
    {
        /** @var ?\Order $order */
        $order = $this->orderRepository->findOneBy([
            'id_order' => $data->getOrderId(),
        ]);

        if (!$order) {
            throw CouldNotProvideUpdateSubscriptionCarrierData::failedToFindOrder($data->getOrderId());
        }

        $subscriptionProduct = $this->subscriptionProductProvider->getProduct($order->getCartProducts());

        if (empty($subscriptionProduct)) {
            throw CouldNotProvideUpdateSubscriptionCarrierData::failedToFindSubscriptionProduct();
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
            $deliveryPrice = $this->subscriptionCarrierDeliveryPriceProvider->getPrice(
                new SubscriptionCarrierDeliveryPriceData(
                    (int) $order->id_address_delivery,
                    (int) $order->id_cart,
                    (int) $order->id_customer,
                    $subscriptionProduct,
                    $data->getSubscriptionCarrierId()
                )
            );
        } catch (\Throwable $exception) {
            throw CouldNotProvideUpdateSubscriptionCarrierData::failedToProvideCarrierDeliveryPrice($exception);
        }

        $orderTotal = (float) $subscriptionProduct['total_price_tax_incl'] + $deliveryPrice;

        /** @var \Currency|null $currency */
        $currency = $this->currencyRepository->findOneBy([
            'id_currency' => (int) $order->id_currency,
        ]);

        if (!$currency) {
            throw CouldNotProvideUpdateSubscriptionCarrierData::failedToFindCurrency((int) $order->id_currency);
        }

        $orderAmount = new Amount($orderTotal, $currency->iso_code);

        return new UpdateSubscriptionData(
            $data->getMollieCustomerId(),
            $data->getMollieSubscriptionId(),
            null,
            $metadata,
            $orderAmount
        );
    }
}
