<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 */

namespace Mollie\Service;

class OrderStateImageService
{
	/**
	 * @var int
	 */
	public function createOrderStateLogo($orderStateId)
	{
		$source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
		$destination = _PS_ORDER_STATE_IMG_DIR_ . $orderStateId . '.gif';
		@copy($source, $destination);
	}

	/**
	 * @var int
	 */
	public function deleteOrderStateLogo($orderStateId)
	{
		$destination = _PS_ORDER_STATE_IMG_DIR_ . $orderStateId . '.gif';
		@unlink($destination);
	}

	/**
	 * @var int
	 */
	public function createTemporaryOrderStateLogo($orderStateId)
	{
		$source = _PS_MODULE_DIR_ . 'mollie/views/img/logo_small.png';
		$destination = _PS_TMP_IMG_DIR_ . 'order_state_mini_' . $orderStateId . '_1.gif';
		@copy($source, $destination);
	}

	/**
	 * @var int
	 */
	public function deleteTemporaryOrderStateLogo($orderStateId)
	{
		$destination = _PS_TMP_IMG_DIR_ . 'order_state_mini_' . $orderStateId . '_1.gif';
		@unlink($destination);
	}
}
