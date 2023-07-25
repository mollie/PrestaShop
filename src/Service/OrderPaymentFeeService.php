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
use MolOrderPaymentFee;
use MolPaymentMethod;
use PrestaShopException;
use Shop;

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

    public function createOrderPaymentFee(int $orderId, int $cartId, PaymentFeeData $paymentFeeData): void
    {
        // TODO remove this method when separate actions for DB will be in use

        $molOrderPaymentFee = new MolOrderPaymentFee();

        $molOrderPaymentFee->id_cart = $cartId;
        $molOrderPaymentFee->id_order = $orderId;
        $molOrderPaymentFee->fee_tax_incl = $paymentFeeData->getPaymentFeeTaxIncl();
        $molOrderPaymentFee->fee_tax_excl = $paymentFeeData->getPaymentFeeTaxExcl();

        try {
            $molOrderPaymentFee->add();
        } catch (\Exception $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);

            // TODO use custom exceptions
            throw new PrestaShopException('Can\'t save Order fee');
        }
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
