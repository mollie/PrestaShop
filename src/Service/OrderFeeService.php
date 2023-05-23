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
use MolOrderFee;
use MolPaymentMethod;
use PrestaShopException;
use Shop;

class OrderFeeService
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

    public function createOrderFee($cartId, $orderFee): void
    {
        $orderFeeObj = new MolOrderFee();

        $orderFeeObj->id_cart = (int) $cartId;
        $orderFeeObj->order_fee = $orderFee;

        try {
            $orderFeeObj->add();
        } catch (\Exception $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);

            throw new PrestaShopException('Can\'t save Order fee');
        }
    }

    public function getPaymentFee(float $totalAmount, string $method): PaymentFeeData
    {
        $environment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
        $paymentId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($method, $environment, $this->shop->id);
        $molPaymentMethod = new MolPaymentMethod($paymentId);

        return $this->paymentFeeProvider->getPaymentFee($molPaymentMethod, $totalAmount);
    }
}
