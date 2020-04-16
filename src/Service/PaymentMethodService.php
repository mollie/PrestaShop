<?php

namespace Mollie\Service;

use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethod;
use Tools;

class PaymentMethodService
{
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var PaymentMethodRepository
     */
    private $methodRepository;

    public function __construct(Mollie $module, PaymentMethodRepository $methodRepository)
    {
        $this->module = $module;
        $this->methodRepository = $methodRepository;
    }

    public function savePaymentMethod($method)
    {
        $paymentId = $this->methodRepository->getPaymentMethodIdByMethodId($method['id']);
        $paymentMethod = new MolPaymentMethod();
        if ($paymentId) {
            $paymentMethod = new MolPaymentMethod($paymentId);
        }
        $paymentMethod->id_method = $method['id'];
        $paymentMethod->method_name = $method['name'];
        $paymentMethod->enabled = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_ENABLED . $method['id']);
        $paymentMethod->title = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_TITLE . $method['id']);
        $paymentMethod->method = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_API . $method['id']);
        $paymentMethod->description = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_DESCRIPTION . $method['id']);
        $paymentMethod->is_countries_applicable = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_APPLICABLE_COUNTRIES . $method['id']);
        $paymentMethod->minimal_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MINIMUM_ORDER_VALUE . $method['id']);
        $paymentMethod->max_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_ORDER_VALUE . $method['id']);
        $paymentMethod->surcharge = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_TYPE . $method['id']);
        $paymentMethod->surcharge_fixed_amount = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT . $method['id']);
        $paymentMethod->surcharge_percentage = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_PERCENTAGE . $method['id']);
        $paymentMethod->surcharge_limit = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_LIMIT . $method['id']);
        $paymentMethod->images_json = json_encode($method['image']);

        $paymentMethod->save();

        return $paymentMethod;
    }
}