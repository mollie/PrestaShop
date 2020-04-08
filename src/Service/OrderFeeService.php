<?php

namespace Mollie\Service;

use Exception;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethod;
use Tools;

class OrderFeeService
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


}