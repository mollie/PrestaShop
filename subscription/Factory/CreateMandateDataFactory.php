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

use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\DTO\CreateMandateData as CreateMandateDataDTO;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateMandateDataFactory
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

    public function buildFromOrder(Order $order): CreateMandateDataDTO
    {
        $customer = $order->getCustomer();
        /** @var \MolCustomer $molCustomer */
        $molCustomer = $this->customerRepository->findOneBy(['email' => $customer->email]);

        $payment = $this->methodRepository->getPaymentBy('cart_id', $order->id_cart);

        return new CreateMandateDataDTO($molCustomer->customer_id, $payment['method'], $molCustomer->name);
    }

    public function build(string $method, string $mollieCustomerId, string $customerName)
    {
        return new CreateMandateDataDTO($mollieCustomerId, $method, $customerName);
    }
}
