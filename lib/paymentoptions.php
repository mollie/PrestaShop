<?php
/**
 * Copyright (c) 2012-2018, Mollie B.V.
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

if (!defined('_PS_VERSION_')) {
    return;
}

$mollie = Module::getInstanceByName('mollie');

if (!$mollie->active) {
    return null;
}

if (!Currency::exists('EUR', 0)) {
    return null;
}

try {
    $methods = $mollie->api->methods->all();
} catch (Mollie_API_Exception $e) {
    if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
        Logger::addLog(__METHOD__.' said: '.$e->getMessage(), Mollie::ERROR);
    }

    return null;
}

$idealIssuers = array();
$issuers = $mollie->getIssuerList();
if (isset($issuers['ideal'])) {
    foreach ($issuers['ideal'] as $issuer) {
        $idealIssuers[$issuer->id] = $issuer->name;
    }
}
Context::getContext()->smarty->assign(array(
    'idealIssuers' => $idealIssuers,
));

$reflection = new ReflectionClass($mollie);
$paymentOptions = array();
foreach ($methods as $method) {
    if ($method->id === 'ideal' && Configuration::get(Mollie::MOLLIE_ISSUERS) == Mollie::ISSUERS_ON_CLICK) {
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption
            ->setCallToActionText($mollie->lang[$method->description])
            ->setAction(Context::getContext()->link->getModuleLink(
                'mollie',
                'payment',
                array('method' => $method->id),
                true
            ))
            ->setInputs(array(
                'token' => array(
                    'name'  => 'issuer',
                    'type'  => 'hidden',
                    'value' => '',
                ),
            ))
            ->setLogo($method->image->normal)
            ->setAdditionalInformation($mollie->display($reflection->getFileName(), 'ideal_dropdown.tpl'))
        ;

        $paymentOptions[] = $newOption;
    } else {
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption
            ->setCallToActionText($mollie->lang[$method->description])
            ->setAction(Context::getContext()->link->getModuleLink(
                'mollie', 'payment',
                array('method' => $method->id), true
            ))
            ->setLogo($method->image->normal)
        ;

        $paymentOptions[] = $newOption;
    }
}

return $paymentOptions;
