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

namespace Mollie\Service\ExpressComponent;

use Mollie\Adapter\API\MollieSessionApiAdapterInterface;
use Mollie\Adapter\Context;
use Mollie\Builder\Express\ExpressSessionRequestBuilder;
use Mollie\Config\Config;
use Mollie\DTO\Express\ExpressSession;
use Mollie\DTO\Express\ExpressSubmitCheck;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\HashUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextFormatUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * One open Checkout Session per cart total. A session's amount is immutable at Mollie, so a
 * changed cart total means a new session; an unchanged total reuses the stored one while it
 * is young enough and still open.
 */
class ExpressSessionService
{
    const FILE_NAME = 'ExpressSessionService';

    /** @var ExpressSessionRequestBuilder */
    private $requestBuilder;
    /** @var MollieSessionApiAdapterInterface */
    private $sessionApi;
    /** @var MolExpressSessionRepositoryInterface */
    private $sessionRepository;
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var ExpressComponentAvailabilityService */
    private $availability;
    /** @var Context */
    private $context;
    /** @var ClockInterface */
    private $clock;
    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(
        ExpressSessionRequestBuilder $requestBuilder,
        MollieSessionApiAdapterInterface $sessionApi,
        MolExpressSessionRepositoryInterface $sessionRepository,
        OrderRepositoryInterface $orderRepository,
        ExpressComponentAvailabilityService $availability,
        Context $context,
        ClockInterface $clock,
        PrestaLoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->sessionApi = $sessionApi;
        $this->sessionRepository = $sessionRepository;
        $this->orderRepository = $orderRepository;
        $this->availability = $availability;
        $this->context = $context;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function getOrCreateSession(\Cart $cart): ExpressSession
    {
        $amount = $this->getCartAmount($cart);
        $currency = \Tools::strtoupper($this->context->getCurrencyIso());
        $stored = $this->sessionRepository->findLatestByCartId((int) $cart->id);

        if ($stored && $this->isReusable($stored, $amount, $currency, $cart)) {
            $session = $this->loadOpenSession($stored);

            if ($session) {
                return $session;
            }
        }

        $session = $this->sessionApi->create($this->requestBuilder->build($cart));

        $this->sessionRepository->deleteByCartId((int) $cart->id);
        $this->sessionRepository->persist($this->buildRow($cart, $session, $amount, $currency));

        $this->logger->debug(sprintf('%s - Express session created', self::FILE_NAME), [
            'cart_id' => (int) $cart->id,
            'session_id' => $session->getId(),
            'amount' => $amount,
        ]);

        return $session;
    }

    public function findStoredSession(\Cart $cart): ?\MolExpressSession
    {
        return $this->sessionRepository->findLatestByCartId((int) $cart->id);
    }

    /**
     * Called from the component's `submit` event, right before Mollie creates the payment: the
     * last moment the shop can refuse a session whose amount no longer matches the cart.
     */
    public function validateBeforeSubmit(\Cart $cart): ExpressSubmitCheck
    {
        if ($this->orderRepository->getOrderIdByCartId((int) $cart->id) > 0) {
            return ExpressSubmitCheck::rejected(ExpressSubmitCheck::REASON_ORDER_EXISTS);
        }

        if (!$this->availability->isAvailable($cart)) {
            return ExpressSubmitCheck::rejected(ExpressSubmitCheck::REASON_CART_NOT_READY);
        }

        $stored = $this->sessionRepository->findLatestByCartId((int) $cart->id);

        if ($stored && !$this->isBuiltForCartOwner($stored, $cart)) {
            $session = $this->getOrCreateSession($cart);

            return ExpressSubmitCheck::rejected(ExpressSubmitCheck::REASON_CART_OWNER_CHANGED, $session->getClientAccessToken());
        }

        if ($stored && NumberUtility::isEqual((float) $stored->amount, (float) $this->getCartAmount($cart))) {
            return ExpressSubmitCheck::ok();
        }

        $session = $this->getOrCreateSession($cart);

        return ExpressSubmitCheck::rejected(ExpressSubmitCheck::REASON_AMOUNT_CHANGED, $session->getClientAccessToken());
    }

    private function isReusable(\MolExpressSession $stored, string $amount, string $currency, \Cart $cart): bool
    {
        if (!NumberUtility::isEqual((float) $stored->amount, (float) $amount)) {
            return false;
        }

        if (!$this->isBuiltForCartOwner($stored, $cart)) {
            return false;
        }

        if (\Tools::strtoupper((string) $stored->currency_iso) !== $currency) {
            return false;
        }

        $age = strtotime($this->clock->getCurrentDate()) - strtotime((string) $stored->created_at);

        if ($age < 0 || $age >= Config::EXPRESS_SESSION_REUSE_MAX_AGE_SECONDS) {
            return false;
        }

        return 0 === $this->orderRepository->getOrderIdByCartId((int) $cart->id);
    }

    /**
     * The webhook URL carries a token of the cart's secure_key. Logging in moves a guest cart to the
     * customer and changes that key, so a session built before then would send its webhook with a
     * token the shop refuses: the shopper pays and no order is created.
     */
    private function isBuiltForCartOwner(\MolExpressSession $stored, \Cart $cart): bool
    {
        return hash_equals($this->getWebhookToken($cart), (string) $stored->webhook_token);
    }

    private function getWebhookToken(\Cart $cart): string
    {
        return HashUtility::hash((string) $cart->secure_key);
    }

    private function loadOpenSession(\MolExpressSession $stored): ?ExpressSession
    {
        try {
            $session = $this->sessionApi->get((string) $stored->session_id);
        } catch (\Throwable $exception) {
            $this->logger->warning(sprintf('%s - Stored express session could not be loaded, creating a new one', self::FILE_NAME), [
                'session_id' => $stored->session_id,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            return null;
        }

        $stored->status = $session->getStatus();
        $stored->updated_at = $this->clock->getCurrentDate();
        $this->sessionRepository->persist($stored);

        return $session->isOpen() ? $session : null;
    }

    private function buildRow(\Cart $cart, ExpressSession $session, string $amount, string $currency): \MolExpressSession
    {
        $now = $this->clock->getCurrentDate();

        $row = new \MolExpressSession();
        $row->id_cart = (int) $cart->id;
        $row->id_shop = $this->context->getShopId();
        $row->session_id = $session->getId();
        $row->amount = (float) $amount;
        $row->currency_iso = $currency;
        $row->webhook_token = $this->getWebhookToken($cart);
        $row->status = $session->getStatus() ?: ExpressSession::STATUS_OPEN;
        $row->created_at = $now;
        $row->updated_at = $now;

        return $row;
    }

    private function getCartAmount(\Cart $cart): string
    {
        return TextFormatUtility::formatNumber($cart->getOrderTotal(true, \Cart::BOTH), Config::API_ROUNDING_PRECISION);
    }
}
