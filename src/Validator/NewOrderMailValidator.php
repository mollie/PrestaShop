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
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Validator;

use Configuration;

class NewOrderMailValidator extends AbstractMailValidator
{

    /**
     * @return int
     */
    public function getSendStatus()
    {
        return (int)Configuration::get(\Mollie\Config\Config::MOLLIE_SEND_NEW_ORDER);
    }

    /**
     * @return int
     */
    public function getSendWhenCreatedStatus()
    {
        return \Mollie\Config\Config::NEW_ORDER_MAIL_SEND_ON_CREATION;
    }

    /**
     * @return int
     */
    public function getSendWhenPaidStatus()
    {
        return \Mollie\Config\Config::NEW_ORDER_MAIL_SEND_ON_PAID;
    }

    /**
     * @return int
     */
    public function getNeverSendStatus()
    {
        return \Mollie\Config\Config::NEW_ORDER_MAIL_SEND_ON_NEVER;
    }

    /**
     * @param int $orderState
     * @return bool
     */
    public function validateOrderState($orderState)
    {
        return (int)Configuration::get(\Mollie\Config\Config::MOLLIE_STATUS_PAID) === $orderState ||
            (int)Configuration::get(\Mollie\Config\Config::STATUS_PS_OS_OUTOFSTOCK_PAID) === $orderState;
    }

    /**
     * @param $orderState
     * @return bool
     */
    public function isNeedToBeSent($orderState)
    {
        if ($this->getSendStatus() !== $this->getSendWhenPaidStatus()) {
            return false;
        }

        return $this->validateOrderState($orderState);
    }
}
