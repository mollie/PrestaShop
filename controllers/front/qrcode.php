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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Repository\PaymentMethodRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

/**
 * TODO check if this is even used as IDEAL QRcode has not worked for a long time.
 * Class MollieQrcodeModuleFrontController.
 *
 * @property Mollie $module
 */
class MollieQrcodeModuleFrontController extends ModuleFrontController
{
    const PENDING = 1;
    const SUCCESS = 2;
    const REFRESH = 3;

    /** @var bool */
    public $ssl = true;
    /** @var bool If false, does not build left page column content and hides it. */
    public $display_column_left = false;
    /** @var bool If false, does not build right page column content and hides it. */
    public $display_column_right = false;

    /**
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        if (Tools::getValue('ajax')) {
            $this->processAjax();
            exit;
        }

        if (Tools::getValue('done')) {
            $canceled = true;
            /** @var PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
            $dbPayment = $paymentMethodRepo->getPaymentBy('cart_id', Tools::getValue('cart_id'));
            if (is_array($dbPayment)) {
                try {
                    $apiPayment = $this->module->api->payments->get($dbPayment['transaction_id']);
                    $canceled = PaymentStatus::STATUS_PAID !== $apiPayment->status;
                } catch (ApiException $e) {
                }
            }

            header('Content-Type: text/html');
            $this->context->smarty->assign([
                'ideal_logo' => __PS_BASE_URI__ . 'modules/mollie/views/img/ideal_logo.png',
                'canceled' => $canceled,
            ]);
            echo $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mollie/views/templates/front/qr_done.tpl');
            exit;
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ApiException
     * @throws Exception
     */
    protected function processAjax()
    {
        switch (Tools::getValue('action')) {
            case 'qrCodeStatus':
                $this->processGetStatus();
                break;
            case 'cartAmount':
                $this->processCartAmount();
                break;
        }

        exit;
    }

    /**
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function processGetStatus()
    {
        header('Content-Type: application/json;charset=UTF-8');
        if (empty($this->context->cart)) {
            exit(json_encode([
                'success' => false,
                'status' => false,
                'amount' => null,
            ]));
        }

        try {
            /** @var PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
            $payment = $paymentMethodRepo->getPaymentBy('transaction_id', Tools::getValue('transaction_id'));
        } catch (PrestaShopDatabaseException $e) {
            exit(json_encode([
                'success' => false,
                'status' => false,
                'amount' => null,
            ]));
        } catch (PrestaShopException $e) {
            exit(json_encode([
                'success' => false,
                'status' => false,
                'amount' => null,
            ]));
        }

        switch ($payment['bank_status']) {
            case PaymentStatus::STATUS_PAID:
            case PaymentStatus::STATUS_AUTHORIZED:
                $status = static::SUCCESS;
                break;
            case PaymentStatus::STATUS_OPEN:
                $status = static::PENDING;
                break;
            default:
                $status = static::REFRESH;
                break;
        }

        $cart = new Cart($payment['cart_id']);
        $amount = (int) ($cart->getOrderTotal(true) * 100);
        exit(json_encode([
            'success' => true,
            'status' => $status,
            'amount' => $amount,
            'href' => $this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                [
                    'id_cart' => (int) $cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order' => Order::getOrderByCartId((int) $cart->id),
                    'key' => $cart->secure_key,
                ]
            ),
        ]));
    }

    /**
     * @throws Exception
     */
    protected function processCartAmount()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var Context $context */
        $context = Context::getContext();
        /** @var Cart $cart */
        $cart = $context->cart;

        $cartTotal = (int) ($cart->getOrderTotal(true) * 100);
        exit(json_encode([
            'success' => true,
            'amount' => $cartTotal,
        ]));
    }
}
