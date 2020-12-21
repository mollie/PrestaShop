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

use Configuration;
use Context;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use MolliePrefix\Mollie\Api\Types\PaymentMethod;

class IssuerService
{
	/**
	 * @var PaymentMethodRepository
	 */
	private $paymentMethodRepository;
	/**
	 * @var Mollie
	 */
	private $module;

	public function __construct(Mollie $module, PaymentMethodRepository $paymentMethodRepository)
	{
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->module = $module;
	}

	public function getIdealIssuers()
	{
		$environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);

		$methodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId(PaymentMethod::IDEAL, $environment);
		$issuersJson = $this->paymentMethodRepository->getPaymentMethodIssuersByPaymentMethodId($methodId);
		$issuers = json_decode($issuersJson, true);
		$issuerList[PaymentMethod::IDEAL] = [];
		$context = Context::getContext();
		foreach ($issuers as $issuer) {
			$issuer['href'] = $context->link->getModuleLink(
				$this->module->name,
				'payment',
				['method' => $methodId, 'issuer' => $issuer['id'], 'rand' => time()],
				true
			);
			$issuerList[PaymentMethod::IDEAL][$issuer['id']] = $issuer;
		}

		return $issuerList;
	}
}
