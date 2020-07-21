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

use _PhpScoper5eddef0da618a\Mollie\Api\Endpoints\OrderEndpoint;
use Address;
use Carrier;
use Configuration;
use Context;
use Country;
use Exception;
use Language;
use MolCarrierInformation;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\MolCarrierInformationRepository;
use Mollie\Repository\OrderShipmentRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\TransactionUtility;
use Order as PrestashopOrder;
use OrderCarrier;
use PrestaShopDatabaseException;
use PrestaShopException;
use PrestaShopLogger;
use Tools;
use Validate;

class ShipmentService
{

    /**
     * @var OrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @var MolCarrierInformationRepository
     */
    private $informationRepository;
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(
        OrderShipmentRepository $orderShipmentRepository,
        MolCarrierInformationRepository $informationRepository,
        PaymentMethodRepository $paymentMethodRepository,
        Mollie $module
    ) {
        $this->orderShipmentRepository = $orderShipmentRepository;
        $this->informationRepository = $informationRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->module = $module;
    }

    /**
     * @param $orderId
     * @param $orderStatusId
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return void
     */
    public function shipOrder($orderId, $orderStatusId)
    {
        $order = new PrestashopOrder($orderId);
        $checkStatuses = [];
        if (Configuration::get(Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkStatuses = @json_decode(Configuration::get(Config::MOLLIE_AUTO_SHIP_STATUSES));
        }
        if (!is_array($checkStatuses)) {
            $checkStatuses = [];
        }
        $shipmentInfo = $this->getShipmentInformation($order->reference);

        if (!(Configuration::get(Config::MOLLIE_AUTO_SHIP_MAIN) && in_array($orderStatusId, $checkStatuses)
            ) || $shipmentInfo === null
        ) {
            return;
        }

        try {
            $dbPayment = $this->paymentMethodRepository->getPaymentBy('order_id', (int)$orderId);
        } catch (PrestaShopDatabaseException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        }
        if (empty($dbPayment) || !isset($dbPayment['transaction_id'])) {
            // No transaction found
            return;
        }

        if (!TransactionUtility::isOrderTransaction($dbPayment['transaction_id'])
        ) {
            // No need to check regular payments
            return;
        }

        try {
            $apiOrder = $this->module->api->orders->get($dbPayment['transaction_id']);
            $shippableItems = 0;
            foreach ($apiOrder->lines as $line) {
                $shippableItems += $line->shippableQuantity;
            }
            if ($shippableItems <= 0) {
                return;
            }

            $apiOrder->shipAll($shipmentInfo);
        } catch (\_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
        }
    }

    /**
     * Get shipment information
     *
     * @param int $idOrder
     *
     * @return array|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 3.3.0
     */
    public function getShipmentInformation($orderReference)
    {
        $order = PrestashopOrder::getByReference($orderReference)[0];
        if (!Validate::isLoadedObject($order)) {
            return null;
        }
        $invoiceAddress = new Address($order->id_address_invoice);
        $deliveryAddress = new Address($order->id_address_delivery);
        $carrierInformationId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($order->id_carrier);
        $carrierInformation = new MolCarrierInformation($carrierInformationId);
        if (!Validate::isLoadedObject($invoiceAddress)
            || !Validate::isLoadedObject($deliveryAddress)
            || !$carrierInformation
        ) {
            return [];
        }

        if ($carrierInformation === Config::MOLLIE_CARRIER_NO_TRACKING_INFO) {
            return [];
        }

        $langId = Context::getContext()->language->id;
        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_MODULE) {
            $carrier = new Carrier($order->id_carrier);
            if (in_array($carrier->external_module_name, ['postnl', 'myparcel'])) {
                $table = $carrier->external_module_name === 'postnl' ? 'postnlmod_order' : 'myparcel_order';

                try {
                    $info = $this->orderShipmentRepository->getShipmentInformation($table, $order->id);
                    if ($info['tracktrace'] && $info['postcode']) {
                        $postcode = Tools::strtoupper(str_replace(' ', '', $info['postcode']));
                        $langIso = Tools::strtoupper(Language::getIsoById($langId));
                        $countryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));
                        $tracktrace = $info['tracktrace'];

                        return [
                            'tracking' => [
                                'carrier' => 'PostNL',
                                'code' => $info['tracktrace'],
                                'url' => "http://postnl.nl/tracktrace/?L={$langIso}&B={$tracktrace}&P={$postcode}&D={$countryIso}&T=C",
                            ],
                        ];
                    }
                } catch (PrestaShopDatabaseException $e) {
                    return [];
                }
            }

            return [];
        }

        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_CARRIER) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }

            if (!$shippingNumber || !$carrier->name) {
                return [];
            }

            return [
                'tracking' => [
                    'carrier' => $carrier->name,
                    'code' => $shippingNumber,
                    'url' => str_replace('@', $shippingNumber, $carrier->url),
                ],
            ];
        }

        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_CUSTOM) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }

            $invoicePostcode = Tools::strtoupper(str_replace(' ', '', $invoiceAddress->postcode));
            $invoiceCountryIso = Tools::strtoupper(Country::getIsoById($invoiceAddress->id_country));
            $deliveryPostcode = Tools::strtoupper(str_replace(' ', '', $deliveryAddress->postcode));
            $deliveryCountryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));

            $langIso = Tools::strtoupper(Language::getIsoById($langId));

            $info = [
                '@' => $shippingNumber,
                '%%shipping_number%%' => $shippingNumber,
                '%%invoice.country_iso%%' => $invoiceCountryIso,
                '%%invoice.postcode%%' => $invoicePostcode,
                '%%delivery.country_iso%%' => $deliveryCountryIso,
                '%%delivery.postcode%%' => $deliveryPostcode,
                '%%lang_iso%%' => $langIso,
            ];

            return [
                'tracking' => [
                    'carrier' => $carrier->name,
                    'code' => $shippingNumber,
                    'url' => str_ireplace(
                        array_keys($info),
                        array_values($info),
                        $carrierInformation->url_source
                    ),
                ],
            ];
        }

        return [];
    }

}