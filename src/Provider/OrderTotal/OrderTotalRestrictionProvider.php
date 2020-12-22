<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Provider;

use Mollie\Repository\MolPaymentMethodOrderTotalRestrictionRepositoryInterface;
use MolPaymentMethod;
use MolPaymentMethodOrderTotalRestriction;

class OrderTotalRestrictionProvider implements OrderTotalRestrictionProviderInterface
{
	/**
	 * @var MolPaymentMethodOrderTotalRestrictionRepositoryInterface
	 */
	private $methodOrderTotalRestriction;

	public function __construct(
		MolPaymentMethodOrderTotalRestrictionRepositoryInterface $methodOrderTotalRestriction
	) {
		$this->methodOrderTotalRestriction = $methodOrderTotalRestriction;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrderTotalMinimumRestriction(MolPaymentMethod $paymentMethod, $currencyIso)
	{
		$paymentMethodOrderTotalRestriction = $this->getPaymentMethodOrderTotalRestriction(
			$paymentMethod->id,
			$currencyIso
		);

		if (!$paymentMethodOrderTotalRestriction) {
			return 0.0;
		}

		return (float) $paymentMethodOrderTotalRestriction->minimum_order_total;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrderTotalMaximumRestriction(MolPaymentMethod $paymentMethod, $currencyIso)
	{
		$paymentMethodOrderTotalRestriction = $this->getPaymentMethodOrderTotalRestriction(
			$paymentMethod->id,
			$currencyIso
		);

		if (!$paymentMethodOrderTotalRestriction) {
			return 0.0;
		}

		return (float) $paymentMethodOrderTotalRestriction->maximum_order_total;
	}

	/**
	 * @param int $id_payment_method
	 * @param string $currencyIso
	 *
	 * @return MolPaymentMethodOrderTotalRestriction|null
	 */
	private function getPaymentMethodOrderTotalRestriction($id_payment_method, $currencyIso)
	{
		/** @var MolPaymentMethodOrderTotalRestriction|null $paymentMethodOrderTotalRestriction */
		$paymentMethodOrderTotalRestriction = $this->methodOrderTotalRestriction->findOneBy([
			'id_payment_method' => (int) $id_payment_method,
			'currency_iso' => strtoupper($currencyIso),
		]);

		return $paymentMethodOrderTotalRestriction;
	}
}
