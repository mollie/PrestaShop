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

namespace Mollie\Presenter;

use Mollie;
use Smarty_Data;

class OrderListActionBuilder
{
	const FILE_NAME = 'OrderListActionBuilder';
	/**
	 * @var Mollie
	 */
	private $mollie;

	public function __construct(Mollie $mollie)
	{
		$this->mollie = $mollie;
	}

	public function buildOrderPaymentResendButton(Smarty_Data $smarty, $orderId)
	{
		$smarty->assign([
			'idOrder' => (int) $orderId,
			'message' => $this->mollie->l(
				'You will resend email with payment link to the customer',
				self::FILE_NAME
			),
			'orderListIcon' => $this->mollie->getPathUri() . 'views/img/second_chance.png',
		]);

		return $this->mollie->display(
			$this->mollie->getLocalPath(),
			'views/templates/hook/admin/order-list-icon.tpl'
		);
	}
}
