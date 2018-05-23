<?php

/**
 * Class MollieQrcodeModuleFrontController
 *
 * @property Mollie $module
 */
class MollieQrcodeModuleFrontController extends ModuleFrontController
{
    /** @var bool If false, does not build left page column content and hides it. */
    public $display_column_left = true;
    /** @var bool If false, does not build right page column content and hides it. */
    public $display_column_right = true;

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if (Tools::getValue('ajax')) {
            $this->processAjax();
        }

        if (Tools::getValue('done')) {
            $this->setTemplate('qr-done.tpl');
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function processAjax()
    {
        header('Content-Type: application/json;charset=UTF-8');

        switch (Tools::getValue('action')) {
            case 'qrCodeNew':
                return $this->processNewQrCode();
            case 'qrCodeStatus':
                return $this->processGetStatus();
        }

        exit;
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function processNewQrCode()
    {
        header('Content-Type: application/json');
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');
        $context = Context::getContext();
        $customer = $context->customer;
        $cart = $context->cart;
        if (!$cart instanceof Cart || !$cart->getOrderTotal(true)) {
            die(json_encode(array(
                'success' => false,
                'message' => 'No active cart',
            )));
        }

        $payment = $mollie->api->payments->create(Mollie::getPaymentData(
            $cart->getOrderTotal(true),
            strtoupper($this->context->currency->iso_code),
            'ideal',
            null,
            (int) $cart->id,
            $customer->secure_key,
            $qrCode = true
        ), array(
            'include' => 'details.qrCode',
        ));
        Db::getInstance()->insert(
            'mollie_payments',
            array(
                'cart_id'        => (int) $cart->id,
                'method'         => $payment->method,
                'transaction_id' => $payment->id,
                'bank_status'    => \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                'created_at'     => date("Y-m-d H:i:s"),
            )
        );

        $src = isset($payment->details->qrCode->src) ? $payment->details->qrCode->src : null;
        die(json_encode(array(
            'success'       => (bool) $src,
            'href'          => $src,
            'idTransaction' => $payment->id,
        )));
    }

    /**
     * Get payment status, can be regularly polled
     *
     * @throws PrestaShopException
     */
    protected function processGetStatus()
    {
        if (empty($this->context->cart)) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
            )));
        }

        try {
            $payment = $this->module->getPaymentBy('transaction_id', Tools::getValue('transaction_id'));
        } catch (PrestaShopDatabaseException $e) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
            )));
        } catch (PrestaShopException $e) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
            )));
        }

        $cart = new Cart($payment['cart_id']);
        die(json_encode(array(
            'success' => true,
            'status'  => $payment['bank_status'] === \Mollie\Api\Types\PaymentStatus::STATUS_PAID,
            'href'    => $this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                array(
                    'id_cart'   => (int) $cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order'  => (int) Order::getOrderByCartId($cart->id),
                    'key'       => $cart->secure_key,
                )
            )
        )));
    }
}
