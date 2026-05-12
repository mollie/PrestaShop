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

use Cart;
use CartRule;
use Configuration;
use Context;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\CartRule\CartRuleQuantityResetHandlerInterface;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\TransactionUtility;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayByBankCancellationService
{
    const TERMINAL_FAILURE_STATUSES = [
        PaymentStatus::STATUS_CANCELED,
        PaymentStatus::STATUS_EXPIRED,
        PaymentStatus::STATUS_FAILED,
    ];

    /** @var \Mollie */
    private $module;

    /** @var LoggerInterface */
    private $logger;

    /** @var PaymentMethodRepository */
    private $paymentMethodRepository;

    /** @var CartRuleQuantityResetHandlerInterface */
    private $cartRuleQuantityResetHandler;

    public function __construct(
        ModuleFactory $moduleFactory,
        LoggerInterface $logger,
        PaymentMethodRepository $paymentMethodRepository,
        CartRuleQuantityResetHandlerInterface $cartRuleQuantityResetHandler
    ) {
        $this->module = $moduleFactory->getModule();
        $this->logger = $logger;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->cartRuleQuantityResetHandler = $cartRuleQuantityResetHandler;
    }

    /**
     * @param string $transactionId
     *
     * @return string
     */
    public function getActualMollieStatus($transactionId)
    {
        try {
            $isOrder = TransactionUtility::isOrderTransaction($transactionId);

            if ($isOrder) {
                $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
                $payments = ArrayUtility::getLastElement($transaction->_embedded->payments);

                return $payments->status;
            }

            $transaction = $this->module->getApiClient()->payments->get($transactionId);

            return $transaction->status;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Mollie payment status: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public function isTerminalFailure($status)
    {
        return in_array($status, self::TERMINAL_FAILURE_STATUSES, true);
    }

    /**
     * @param string $mollieStatus
     *
     * @return bool
     */
    public function shouldCancelPayment($mollieStatus)
    {
        return $this->isTerminalFailure($mollieStatus) || $mollieStatus === PaymentStatus::STATUS_OPEN;
    }

    /**
     * @param string $mollieStatus
     *
     * @return string
     */
    public function resolveCancelStatus($mollieStatus)
    {
        return $mollieStatus === PaymentStatus::STATUS_OPEN ? PaymentStatus::STATUS_CANCELED : $mollieStatus;
    }

    /**
     * @param int $cartId
     * @param string $transactionId
     * @param string $mollieStatus
     *
     * @return void
     */
    public function cancelOrderAndRestoreCart($cartId, $transactionId, $mollieStatus)
    {
        $context = Context::getContext();
        $orderId = (int) Order::getIdByCartId($cartId);
        $canceledStateId = (int) Configuration::get(Config::MOLLIE_STATUS_CANCELED);

        if ($orderId) {
            $order = new Order($orderId);

            if ((int) $order->current_state !== $canceledStateId) {
                $order->setCurrentState($canceledStateId);
                $this->logger->info(sprintf('Pay by Bank payment canceled, order %d set to canceled', $orderId));
            }
        }

        $this->paymentMethodRepository->savePaymentStatus(
            $transactionId,
            $mollieStatus,
            $orderId,
            Config::PAY_BY_BANK
        );

        $currentCartId = (int) $context->cookie->id_cart;

        if ($currentCartId > 0 && $currentCartId !== (int) $cartId && !Order::getIdByCartId($currentCartId)) {
            $this->logger->info(sprintf('Pay by Bank cart %d already restored (current cart: %d), skipping duplication', $cartId, $currentCartId));

            return;
        }

        $cart = new Cart($cartId);

        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        if (!empty($cartRules)) {
            $this->cartRuleQuantityResetHandler->handle($cart, $cartRules);
            $this->logger->info(sprintf('Cart rule quantities restored for canceled Pay by Bank order %d', $orderId));
        }

        $duplicatedCart = $cart->duplicate();
        if ($duplicatedCart && $duplicatedCart['success']) {
            $newCart = $duplicatedCart['cart'];

            foreach ($cartRules as $cartRule) {
                $newCart->addCartRule((int) $cartRule['id_cart_rule']);
            }

            CartRule::autoAddToCart($context);

            $context->cart = $newCart;
            $context->cookie->id_cart = $newCart->id;
            $context->cookie->write();
            $this->logger->info(sprintf('Cart restored for canceled Pay by Bank payment, new cart %d', $newCart->id));
        }
    }

    /**
     * @param int $customerId
     *
     * @return array|false
     */
    public function findPendingPayByBankPayment($customerId)
    {
        return $this->paymentMethodRepository->getLatestPaymentByCustomerAndMethod(
            $customerId,
            Config::PAY_BY_BANK,
            [PaymentStatus::STATUS_OPEN, PaymentStatus::STATUS_PENDING]
        );
    }

    /**
     * @param int $customerId
     *
     * @return void
     */
    public function handleAbandonedPayment($customerId)
    {
        $pendingPayment = $this->findPendingPayByBankPayment($customerId);

        if (!$pendingPayment) {
            return;
        }

        $transactionId = $pendingPayment['transaction_id'];
        $mollieStatus = $this->getActualMollieStatus($transactionId);

        if (empty($mollieStatus)) {
            $this->logger->warning(sprintf('Pay by Bank: could not resolve Mollie status for transaction %s, skipping', $transactionId));

            return;
        }

        if ($this->shouldCancelPayment($mollieStatus)) {
            $this->cancelOrderAndRestoreCart(
                (int) $pendingPayment['cart_id'],
                $transactionId,
                $this->resolveCancelStatus($mollieStatus)
            );
        }
    }
}
