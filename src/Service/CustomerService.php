<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Cart;
use MolCustomer;
use Mollie;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Repository\MolCustomerRepository;

class CustomerService
{
	/**
	 * @var Mollie
	 */
	private $mollie;

	/**
	 * @var MolCustomerRepository
	 */
	private $customerRepository;

	public function __construct(Mollie $mollie, MolCustomerRepository $customerRepository)
	{
		$this->mollie = $mollie;
		$this->customerRepository = $customerRepository;
	}

	public function processCustomerCreation(Cart $cart, $method)
	{
		if (!$this->isSingleCLickPaymentEnabled($method)) {
			return false;
		}

		$customer = new \Customer($cart->id_customer);

		$fullName = "{$customer->firstname} {$customer->lastname}";
		/** @var MolCustomer|null $molCustomer */
		$molCustomer = $this->customerRepository->findOneBy(
			[
				'name' => $fullName,
				'email' => $customer->email,
			]
		);

		if ($molCustomer) {
			return $this->mollie->api->customers->get($molCustomer->customer_id);
		}

		$mollieCustomer = $this->createCustomer($fullName, $customer->email);

		$molCustomer = new MolCustomer();
		$molCustomer->name = $fullName;
		$molCustomer->email = $customer->email;
		$molCustomer->customer_id = $mollieCustomer->id;
		$molCustomer->created_at = $mollieCustomer->createdAt;

		$molCustomer->add();

		return $mollieCustomer;
	}

	public function createCustomer($name, $email)
	{
		try {
			return $this->mollie->api->customers->create(
				[
					'name' => $name,
					'email' => $email,
				]
			);
		} catch (\Exception $e) {
			throw new MollieException('Failed to create Mollie customer', MollieException::CUSTOMER_EXCEPTION, $e);
		}
	}

	public function isSingleCLickPaymentEnabled($method)
	{
		if (PaymentMethod::CREDITCARD !== $method) {
			return false;
		}
		$isComponentEnabled = \Configuration::get(Config::MOLLIE_IFRAME);
		$isSingleClickPaymentEnabled = \Configuration::get(Config::MOLLIE_SINGLE_CLICK_PAYMENT);
		if (!$isComponentEnabled && $isSingleClickPaymentEnabled) {
			return true;
		}

		return false;
	}
}
