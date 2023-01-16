<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\DTO\CreateMandateData as CreateMandateDataDTO;
use Order;

class CreateMandateData
{
    /** @var MolCustomerRepository */
    private $customerRepository;

    /** @var PaymentMethodRepositoryInterface */
    private $methodRepository;

    public function __construct(
        MolCustomerRepository $customerRepository,
        PaymentMethodRepositoryInterface $methodRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->methodRepository = $methodRepository;
    }

    public function build(Order $order): CreateMandateDataDTO
    {
        $customer = $order->getCustomer();
        /** @var \MolCustomer $molCustomer */
        $molCustomer = $this->customerRepository->findOneBy(['email' => $customer->email]);

        $payment = $this->methodRepository->getPaymentBy('cart_id', $order->id_cart);
        $createMandateData = new CreateMandateDataDTO($molCustomer->customer_id, $payment['method'], $molCustomer->name);

        return $createMandateData;
    }
}