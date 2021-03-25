<?php

namespace Mollie\Builder\Content\PaymentOption;

use Mollie;
use Mollie\Builder\TemplateBuilderInterface;
use Mollie\Service\IssuerService;
use \Mollie\Api\Types\PaymentMethod;

class IdealDropdownInfoBlock implements TemplateBuilderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var IssuerService
	 */
	private $issuerService;

	public function __construct(Mollie $module, IssuerService $issuerService)
	{
		$this->module = $module;
		$this->issuerService = $issuerService;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildParams()
	{
		return [
			'idealIssuers' => $this->getIdealIssuers(),
		];
	}

	/**
	 * @return array
	 */
	private function getIdealIssuers()
	{
		$issuers = $this->issuerService->getIdealIssuers();

		return isset($issuers[PaymentMethod::IDEAL]) ? $issuers[PaymentMethod::IDEAL] : [];
	}
}
