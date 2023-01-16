<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\CurrencyRepository as CurrencyAdapter;
use Order;
use Product;

class CreateSubscriptionDataFactory
{
    /** @var MolCustomerRepository */
    private $customerRepository;

    /** @var SubscriptionIntervalProvider */
    private $subscriptionInterval;

    /** @var SubscriptionDescriptionProvider */
    private $subscriptionDescription;

    /** @var CurrencyAdapter */
    private $currencyAdapter;

    /** @var CombinationRepository */
    private $combination;

    /** @var PaymentMethodRepositoryInterface */
    private $methodRepository;

    public function __construct(
        MolCustomerRepository $customerRepository,
        SubscriptionIntervalProvider $subscriptionInterval,
        SubscriptionDescriptionProvider $subscriptionDescription,
        CurrencyAdapter $currencyAdapter,
        CombinationRepository $combination,
        PaymentMethodRepositoryInterface $methodRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriptionInterval = $subscriptionInterval;
        $this->subscriptionDescription = $subscriptionDescription;
        $this->currencyAdapter = $currencyAdapter;
        $this->combination = $combination;
        $this->methodRepository = $methodRepository;
    }

    public function build(Order $order): SubscriptionDataDTO
    {
        $customer = $order->getCustomer();
        /** @var \MolCustomer $molCustomer */
        //todo: will need to improve mollie module logic to have shop id or card it so that multishop doesn't break
        $molCustomer = $this->customerRepository->findOneBy(['email' => $customer->email]);

        $products = $order->getCartProducts();

        // only one product is expected to be in order for subscription, if there are more than validation failed.
        if (count($products) !== 1) {
            throw new SubscriptionProductValidationException('Invalid amount of products for subscription', SubscriptionProductValidationException::MULTTIPLE_PRODUCTS_IN_CART);
        }
        /** @var Product $product */
        $product = reset($products);
        $combination = $this->combination->getById((int) $product['id_product_attribute']);
        $interval = $this->subscriptionInterval->getSubscriptionInterval($combination);

        $currency = $this->currencyAdapter->getById((int) $order->id_currency);
        $description = $this->subscriptionDescription->getSubscriptionDescription($order, $currency->iso_code);

        $orderAmount = new Amount((float) $order->total_paid_tax_incl, $currency->iso_code);
        $subscriptionData = new SubscriptionDataDTO(
            $molCustomer->customer_id,
            $orderAmount,
            $interval,
            $description
        );

        // todo: check for solution what to do when mandate is missing
        $payment = $this->methodRepository->getPaymentBy('cart_id', $order->id_cart);
        $subscriptionData->setMandateId($payment['mandate_id']);

        return $subscriptionData;
    }
}
