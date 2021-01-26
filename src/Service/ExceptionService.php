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
 */

namespace Mollie\Service;

use Exception;
use Mollie;
use Mollie\Exception\OrderCreationException;
use Mollie\Exception\OrderTotalRestrictionException;
use Mollie\Exception\ShipmentCannotBeSentException;

class ExceptionService
{
	const SHORT_CLASS_NAME = 'ExceptionService';

	/**
	 * @var Mollie
	 */
	private $module;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
	}

	public function getErrorMessages()
	{
		return [
			OrderCreationException::class => [
					OrderCreationException::DEFAULT_ORDER_CREATION_EXCEPTION => $this->module->l('An error occurred while initializing your payment. Please contact our customer support.', self::SHORT_CLASS_NAME),
					OrderCreationException::WRONG_BILLING_PHONE_NUMBER_EXCEPTION => $this->module->l('It looks like you have entered incorrect phone number format in billing address step. Please change the number and try again.', self::SHORT_CLASS_NAME),
					OrderCreationException::WRONG_SHIPPING_PHONE_NUMBER_EXCEPTION => $this->module->l('It looks like you have entered incorrect phone number format in shipping address step. Please change the number and try again.', self::SHORT_CLASS_NAME),
					OrderCreationException::ORDER_TOTAL_LOWER_THAN_MINIMUM => $this->module->l('Chosen payment option is unavailable for your order total amount. Please consider using other payment option and try again.', self::SHORT_CLASS_NAME),
					OrderCreationException::ORDER_TOTAL_HIGHER_THAN_MAXIMUM => $this->module->l('Chosen payment option is unavailable for your order total amount. Please consider using other payment option and try again.', self::SHORT_CLASS_NAME),
			],
			ShipmentCannotBeSentException::class => [
				ShipmentCannotBeSentException::NO_SHIPPING_INFORMATION => $this->module->l(
					'Shipment information cannot be sent. Order reference (%s) has no shipping information.'
				),
				ShipmentCannotBeSentException::AUTOMATIC_SHIPMENT_SENDER_IS_NOT_AVAILABLE => $this->module->l(
					'Shipment information cannot be sent. Order reference (%s) does not have automatic shipment sender available.'
				),
				ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION => $this->module->l(
					'Shipment information cannot be sent. Order reference (%s) has no payment information.'
				),
				ShipmentCannotBeSentException::PAYMENT_IS_NOT_ORDER => $this->module->l(
					'Shipment information cannot be sent. Order reference (%s) is a regular payment.'
				),
			],
			OrderTotalRestrictionException::class => [
				OrderTotalRestrictionException::NO_AVAILABLE_PAYMENT_METHODS_FOUND => $this->module->l(
					'Failed to refresh order total restriction values: None available payment methods were found', self::SHORT_CLASS_NAME
				),
				OrderTotalRestrictionException::NO_AVAILABLE_CURRENCIES_FOUND => $this->module->l(
					'Failed to refresh order total restriction values: None available currencies were found', self::SHORT_CLASS_NAME
				),
				OrderTotalRestrictionException::ORDER_TOTAL_RESTRICTION_SAVE_FAILED => $this->module->l(
					'Failed to save payment method order restriction', self::SHORT_CLASS_NAME
				),
			],
		];
	}

	public function getErrorMessageForException(Exception $exception, array $messages, array $params = [])
	{
		$exceptionType = get_class($exception);
		$exceptionCode = $exception->getCode();

		if (isset($messages[$exceptionType])) {
			$message = $messages[$exceptionType];

			if (is_string($message)) {
				return $message;
			}

			if (is_array($message) && isset($message[$exceptionCode])) {
				if (strpos($message[$exceptionCode], '%') !== false) {
					return sprintf($message[$exceptionCode], implode(',', $params));
				}

				return $message[$exceptionCode];
			}
		}

		return $this->module->l('Unknown exception in Mollie');
	}
}
