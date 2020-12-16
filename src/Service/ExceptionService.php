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
 */

namespace Mollie\Service;

use Exception;
use Mollie;
use Mollie\Exception\OrderCreationException;

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
		];
	}

	public function getErrorMessageForException(Exception $exception, array $messages)
	{
		$exceptionType = get_class($exception);
		$exceptionCode = $exception->getCode();

		if (isset($messages[$exceptionType])) {
			$message = $messages[$exceptionType];

			if (is_string($message)) {
				return $message;
			}

			if (is_array($message) && isset($message[$exceptionCode])) {
				return $message[$exceptionCode];
			}
		}

		return $this->module->l('Unknown exception in Mollie');
	}
}
