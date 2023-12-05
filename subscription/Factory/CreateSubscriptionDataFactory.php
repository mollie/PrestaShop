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

namespace Mollie\Subscription\Factory;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\SubscriptionOrderAmountProviderData;
use Mollie\Subscription\Exception\CouldNotCreateSubscriptionData;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Provider\SubscriptionOrderAmountProvider;
use Mollie\Utility\SecureKeyUtility;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateSubscriptionDataFactory
{
    /** @var MolCustomerRepository */
    private $customerRepository;
    /** @var SubscriptionIntervalProvider */
    private $subscriptionInterval;
    /** @var SubscriptionDescriptionProvider */
    private $subscriptionDescription;
    /** @var PaymentMethodRepositoryInterface */
    private $methodRepository;
    /** @var Mollie */
    private $module;
    /** @var Context */
    private $context;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;

    public function __construct(
        MolCustomerRepository $customerRepository,
        SubscriptionIntervalProvider $subscriptionInterval,
        SubscriptionDescriptionProvider $subscriptionDescription,
        PaymentMethodRepositoryInterface $methodRepository,
        Mollie $module,
        Context $context,
        ConfigurationAdapter $configuration,
        SubscriptionOrderAmountProvider $subscriptionOrderAmountProvider
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriptionInterval = $subscriptionInterval;
        $this->subscriptionDescription = $subscriptionDescription;
        $this->methodRepository = $methodRepository;
        $this->module = $module;
        $this->context = $context;
        $this->configuration = $configuration;
        $this->subscriptionOrderAmountProvider = $subscriptionOrderAmountProvider;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function build(Order $order, array $subscriptionProduct): SubscriptionDataDTO
    {
        // TODO modify mol_customer table to hold id_customer (default PS customer ID as it holds id_shop). Then we won't need separate id_shop and id_shop_group column

        try {
            /** @var \MolCustomer|null $molCustomer */
            $molCustomer = $this->customerRepository->findOneBy([
                'email' => $order->getCustomer()->email,
            ]);
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscriptionData::unknownError($exception);
        }

        if (!$molCustomer) {
            throw CouldNotCreateSubscriptionData::failedToFindMollieCustomer((string) $order->getCustomer()->email);
        }

        try {
            $interval = $this->subscriptionInterval->getSubscriptionInterval((int) $subscriptionProduct['id_product_attribute']);
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscriptionData::failedToRetrieveSubscriptionInterval($exception, (int) $subscriptionProduct['id_product_attribute']);
        }

        $description = $this->subscriptionDescription->getSubscriptionDescription($order);

        $subscriptionCarrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        try {
            $orderAmount = $this->subscriptionOrderAmountProvider->get(
                SubscriptionOrderAmountProviderData::create(
                    (int) $order->id_address_delivery,
                    (int) $order->id_cart,
                    (int) $order->id_customer,
                    $subscriptionProduct,
                    $subscriptionCarrierId,
                    (int) $order->id_currency
                )
            );
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscriptionData::failedToProvideSubscriptionOrderAmount();
        }

        $subscriptionData = new SubscriptionDataDTO(
            $molCustomer->customer_id,
            $orderAmount,
            $interval,
            $description
        );

        $subscriptionData->setWebhookUrl($this->context->getModuleLink(
            'mollie',
            'subscriptionWebhook'
        ));

        $secureKey = SecureKeyUtility::generateReturnKey(
            $order->id_customer,
            $order->id_cart,
            $this->module->name
        );

        $subscriptionData->setMetaData([
            'secure_key' => $secureKey,
            'subscription_carrier_id' => $subscriptionCarrierId,
        ]);

        // todo: check for solution what to do when mandate is missing
        $payment = $this->methodRepository->getPaymentBy('cart_id', $order->id_cart);

        $subscriptionData->setMandateId($payment['mandate_id']);

        return $subscriptionData;
    }
}
