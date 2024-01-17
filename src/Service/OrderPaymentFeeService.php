<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Configuration;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use MolPaymentMethod;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderPaymentFeeService
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var Shop
     */
    private $shop;
    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        Shop $shop,
        PaymentFeeProviderInterface $paymentFeeProvider
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shop = $shop;
        $this->paymentFeeProvider = $paymentFeeProvider;
    }

    public function getPaymentFee(float $totalAmount, string $method): PaymentFeeData
    {
        // TODO order and payment fee in same service? Separate logic as this is probably used in cart context

        $environment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
        $paymentId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($method, $environment, $this->shop->id);
        $molPaymentMethod = new MolPaymentMethod($paymentId);

        return $this->paymentFeeProvider->getPaymentFee($molPaymentMethod, $totalAmount);
    }
}
