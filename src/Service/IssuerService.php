<?php

namespace Mollie\Service;

use Context;
use Mollie;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Repository\PaymentMethodRepository;

class IssuerService
{
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module, PaymentMethodRepository $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->module = $module;
    }

    public function getIdealIssuers()
    {
        $methodId = PaymentMethod::IDEAL;
        $issuersJson = $this->paymentMethodRepository->getPaymentMethodIssuersByPaymentMethodId($methodId);
        $issuers = json_decode($issuersJson, true);
        $issuerList[PaymentMethod::IDEAL] = [];
        $context = Context::getContext();
        foreach ($issuers as $issuer) {
            $issuer['href'] = $context->link->getModuleLink(
                $this->module->name,
                'payment',
                ['method' => $methodId , 'issuer' => $issuer['id'], 'rand' => time()],
                true
            );
            $issuerList[PaymentMethod::IDEAL][$issuer['id']] = $issuer;
        }

        return $issuerList;
    }
}