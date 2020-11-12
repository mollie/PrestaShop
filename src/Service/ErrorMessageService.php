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
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */

namespace Mollie\Service;

use JsonSerializable;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;

class ErrorMessageService
{
    const NAME = 'ErrorMessageService';
    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * ErrorMessageService constructor.
     * @param Mollie $module
     * @param ConfigurationAdapter $configurationAdapter
     */
    public function __construct(Mollie $module, ConfigurationAdapter $configurationAdapter)
    {
        $this->module = $module;
        $this->configurationAdapter = $configurationAdapter;
    }

    public function getPaymentErrorMessage($message, JsonSerializable $paymentData)
    {
        $errorMessage = $this->module->l('An error occurred while initializing your payment. Please contact our customer support.', self::NAME);
        if (strpos($message, 'billingAddress.phone')) {
            $errorMessage = $this->module->l('It looks like you have entered incorrect phone number format in billing address step. Please change the number and try again.', self::NAME);
        } elseif (strpos($message, 'shippingAddress.phone')) {
            $errorMessage = $this->module->l('It looks like you have entered incorrect phone number format in shipping address step. Please change the number and try again.', self::NAME);
        }

        return $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)
            ? $message.'. Cart Dump: '.json_encode($paymentData, JSON_PRETTY_PRINT)
            : $errorMessage;
    }
}
