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

use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\Payment;
use Mollie\Utility\EnvironmentUtility;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

class CancelService
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var TransactionService
	 */
	private $transactionService;

	public function __construct(Mollie $module, TransactionService $transactionService)
	{
		$this->module = $module;
		$this->transactionService = $transactionService;
	}

	/**
	 * @param string $transactionId
	 * @param array $lines
	 *
	 * @return array
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.0
	 */
	public function doCancelOrderLines($transactionId, $lines = [])
	{
		try {
			/** @var Order $payment */
			$order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
			if ([] === $lines) {
				$order->cancel();
			} else {
				$cancelableLines = [];
				foreach ($lines as $line) {
					$cancelableLines[] = ['id' => $line['id'], 'quantity' => $line['quantity']];
				}
				$order->cancelLines(['lines' => $cancelableLines]);
			}

			if (EnvironmentUtility::isLocalEnvironment()) {
				// Refresh payment on local environments
				/** @var Payment $payment */
				$apiPayment = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
				if (!Tools::isSubmit('module')) {
					$_GET['module'] = $this->module->name;
				}
				$this->transactionService->processTransaction($apiPayment);
			}
		} catch (ApiException $e) {
			return [
				'success' => false,
				'message' => $this->module->l('The product(s) could not be canceled!'),
				'detailed' => $e->getMessage(),
			];
		}

		return [
			'success' => true,
			'message' => '',
			'detailed' => '',
		];
	}
}
