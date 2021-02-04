<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Mollie;
use Mollie\Config\Config;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandlerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Order;

class PaymentReturnService
{
	const PENDING = 1;
	const DONE = 2;
	const FILE_NAME = 'PaymentReturnService';

	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * @var CartDuplicationService
	 */
	private $cartDuplicationService;

	/**
	 * @var PaymentMethodRepository
	 */
	private $paymentMethodRepository;

	/**
	 * @var RepeatOrderLinkFactory
	 */
	private $orderLinkFactory;

	/**
	 * @var TransactionService
	 */
	private $transactionService;

	/**
	 * @var CartRuleQuantityChangeHandlerInterface
	 */
	private $cartRuleQuantityChangeHandlerInterface;

	public function __construct(
		Mollie $module,
		CartDuplicationService $cartDuplicationService,
		PaymentMethodRepository $paymentMethodRepository,
		RepeatOrderLinkFactory $orderLinkFactory,
		TransactionService $transactionService,
		CartRuleQuantityChangeHandlerInterface $cartRuleQuantityChangeHandlerInterface
	) {
		$this->module = $module;
		$this->context = Context::getContext();
		$this->cartDuplicationService = $cartDuplicationService;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->orderLinkFactory = $orderLinkFactory;
		$this->transactionService = $transactionService;
		$this->cartRuleQuantityChangeHandlerInterface = $cartRuleQuantityChangeHandlerInterface;
	}

	public function handleStatus(Order $order, $transaction, $status)
	{
		$cart = new Cart($order->id_cart);

		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

		return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handlePendingStatus(Order $order, $transaction)
	{
		$cart = new Cart($order->id_cart);
		$status = static::PENDING;

		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

		return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handlePaidStatus(Order $order, $transaction)
	{
		$cart = new Cart($order->id_cart);
		$status = static::DONE;

		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

		return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handleAuthorizedStatus(Order $order, $transaction)
	{
		$cart = new Cart($order->id_cart);
		$status = static::DONE;

		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

		return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handleFailedStatus(Order $order, $transaction, $paymentMethod)
	{
		if (null !== $paymentMethod) {
			$this->cartDuplicationService->restoreCart($order->id_cart, Config::RESTORE_CART_BACKTRACE_RETURN_CONTROLLER);

			$warning[] = $this->module->l('Your payment was not successful, please try again.', self::FILE_NAME);

			$this->context->cookie->__set('mollie_payment_canceled_error', json_encode($warning));
		}

		$orderLink = $this->orderLinkFactory->getLink();

		return [
			'success' => true,
			'status' => static::DONE,
			'response' => json_encode($transaction),
			'href' => $orderLink,
		];
	}

	private function getStatusResponse($transaction, $status, $cartId, $cartSecureKey)
	{
		/* @phpstan-ignore-next-line */
		$orderId = (int) Order::getOrderByCartId((int) $cartId);

		$successUrl = $this->context->link->getPageLink(
			'order-confirmation',
			true,
			null,
			[
				'id_cart' => (int) $cartId,
				'id_module' => (int) $this->module->id,
				'id_order' => $orderId,
				'key' => $cartSecureKey,
			]
		);

		return [
			'success' => true,
			'status' => $status,
			'response' => json_encode($transaction),
			'href' => $successUrl,
		];
	}
}
