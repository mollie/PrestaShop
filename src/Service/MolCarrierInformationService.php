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

namespace Mollie\Service;

use Carrier;
use Context;
use MolCarrierInformation;
use Mollie\Config\Config;
use Mollie\Repository\MolCarrierInformationRepository;

class MolCarrierInformationService
{
    /**
     * @var MolCarrierInformationRepository
     */
    private $informationRepository;

    public function __construct(MolCarrierInformationRepository $informationRepository)
    {
        $this->informationRepository = $informationRepository;
    }

    public function getAllCarriersInformation($langId = null)
    {
        if (!$langId) {
            $langId = Context::getContext()->language->id;
        }

        $carriers = Carrier::getCarriers(
            $langId,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );

        $configCarriers = array();
        /** @var Carrier $carrier */
        foreach ($carriers as $carrier) {
            $carrier = new Carrier($carrier['id_carrier']);
            $carrierInfoId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($carrier->id);
            if ($carrierInfoId) {
                $carrierInfo = new MolCarrierInformation($carrierInfoId);
                $configCarriers[] = [
                    'id_carrier' => $carrier->id,
                    'name' => $carrier->name,
                    'source' => $carrierInfo->url_source,
                    'module' => $carrier->external_module_name,
                    'module_name' => $carrier->external_module_name,
                    'custom_url' => $carrierInfo->custom_url,
                ];
            } else {
                $configCarriers[] = [
                    'id_carrier' => $carrier->id,
                    'name' => $carrier->name,
                    'source' =>$carrier->external_module_name ? Config::MOLLIE_CARRIER_MODULE : Config::MOLLIE_CARRIER_CARRIER,
                    'module' => $carrier->external_module_name,
                    'module_name' => $carrier->external_module_name,
                    'custom_url' => '',
                ];
            }
        }

        return $configCarriers;
    }

    public function saveMolCarrierInfo($carrierId, $urlSource, $customUrl = null)
    {
        $carrierInformationId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($carrierId);
        $carrierInformation = new MolCarrierInformation($carrierInformationId);
        $carrierInformation->id_carrier = $carrierId;
        $carrierInformation->url_source = $urlSource;
        $carrierInformation->custom_url = $customUrl;

        $carrierInformation->save();
    }
}