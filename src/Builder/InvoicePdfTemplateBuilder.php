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

namespace Mollie\Builder;

use Cart;
use Currency;
use Mollie\Repository\OrderFeeRepository;
use MolOrderFee;
use Order;
use Tools;

final class InvoicePdfTemplateBuilder implements TemplateBuilderInterface
{
	private $orderFeeRepository;

	/**
	 * @var Order
	 */
	private $order;

	public function __construct(OrderFeeRepository $orderFeeRepository)
	{
		$this->orderFeeRepository = $orderFeeRepository;
	}

	public function setOrder(Order $order)
	{
		$this->order = $order;

		return $this;
	}

	public function buildParams()
	{
		$orderFeeId = $this->orderFeeRepository->getOrderFeeIdByCartId(Cart::getCartIdByOrderId($this->order->id));

		$orderFee = new MolOrderFee($orderFeeId);

		if (!$orderFee->order_fee) {
			return [];
		}

		return [
			'orderFeeAmountDisplay' => Tools::displayPrice(
				$orderFee->order_fee,
				new Currency($this->order->id_currency)
			),
		];
	}
}
