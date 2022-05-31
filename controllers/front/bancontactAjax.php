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

use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\Exception\OrderCreationException;
use Mollie\Exception\RetryOverException;
use Mollie\Handler\RetryHandlerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\PaymentMethodService;
use Mollie\Utility\OrderNumberUtility;
use Mollie\Utility\OrderRecoverUtility;

class MollieBancontactAjaxModuleFrontController extends ModuleFrontController
{
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'createTransaction':
                $this->createTransaction();
            case 'checkForPaidTransaction':
                $this->checkForPaidTransaction();
        }
    }

    private function createTransaction()
    {
        /** @var PaymentMethodService $paymentMethodService */
        $paymentMethodService = $this->module->getMollieContainer(PaymentMethodService::class);
        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getMollieContainer(PaymentMethodRepositoryInterface::class);

        /** @var MolPaymentMethod|null $paymentMethod */
        $paymentMethod = $paymentMethodRepository->findOneBy(
            [
                'id_method' => PaymentMethod::BANCONTACT,
                'live_environment' => Configuration::get(Config::MOLLIE_ENVIRONMENT),
            ]
        );

        $cart = Context::getContext()->cart;
        $currency = new Currency($cart->id_currency);
        $orderNumber = OrderNumberUtility::generateOrderNumber($cart->id);

        $paymentData = $paymentMethodService->getPaymentData(
            $cart->getOrderTotal(),
            $currency->iso_code,
            PaymentMethod::BANCONTACT,
            null,
            $cart->id,
            $cart->secure_key,
            $paymentMethod,
            $orderNumber
        );
        $newPayment = $this->module->api->payments->create($paymentData->jsonSerialize(), ["include" => "details.qrCode"]);

        $this->ajaxDie(json_encode(
            [
                'qr_code' => $newPayment->details->qrCode->src
            ]
        ));
    }

    private function checkForPaidTransaction()
    {
        /** @var RetryHandlerInterface $retryHandler */
        $retryHandler = $this->module->getMollieContainer(RetryHandlerInterface::class);
        $cart = Context::getContext()->cart;

        $proc = function () use ($cart) {
            $orderId = Order::getOrderByCartId($cart->id);
            /* @phpstan-ignore-next-line */
            if (!$orderId) {
                throw new OrderCreationException('Order was not created in webhook', OrderCreationException::ORDER_IS_NOT_CREATED);
            }

            return $orderId;
        };

        try {
            $orderId = $retryHandler->retry(
                $proc,
                [
                    'max' => Config::BANCONTACT_ORDER_CREATION_MAX_WAIT_RETRIES,
                    'accepted_exception' => OrderCreationException::class,
                ]
            );
        } catch (RetryOverException $e) {
            $this->ajaxDie(json_encode(
                [
                    'success' => false
                ]
            ));
        }

        if (!$orderId) {
            $this->ajaxDie(json_encode(
                [
                    'success' => false
                ]
            ));
        }

        $successUrl = Context::getContext()->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => $orderId,
                'key' => $cart->secure_key,
            ]
        );
        OrderRecoverUtility::recoverCreatedOrder($this->context, $cart->id_customer);

        $this->ajaxDie(json_encode(
            [
                'success' => true,
                'redirectUrl' => $successUrl
            ]
        ));
    }
}
