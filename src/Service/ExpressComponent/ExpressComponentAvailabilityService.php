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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Decides whether the Express Component may render, and which of its buttons. The Sessions API
 * beta has no test mode, so the component is live-mode only.
 */
class ExpressComponentAvailabilityService
{
    const FILE_NAME = 'ExpressComponentAvailabilityService';

    const VISIBILITY_AUTOMATIC = 'automatic';
    const VISIBILITY_HIDDEN = 'hidden';

    /**
     * Component button => [config flag, mol_payment_method id_method the button settles through].
     * Google Pay has no row of its own; its payments are card payments.
     */
    const BUTTONS = [
        'applepay' => [Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY, 'applepay'],
        'googlepay' => [Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY, 'creditcard'],
        'paypal' => [Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL, 'paypal'],
    ];

    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var Context */
    private $context;
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;
    /** @var SubscriptionOrderValidator */
    private $subscriptionOrderValidator;
    /** @var ModuleFactory */
    private $moduleFactory;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var string */
    private $unavailableReason = '';
    /** @var array<int, bool> hookDisplayHeader and hookDisplayPaymentTop ask for the same cart in one request */
    private $cartAvailability = [];

    public function __construct(
        ConfigurationAdapter $configuration,
        Context $context,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        SubscriptionOrderValidator $subscriptionOrderValidator,
        ModuleFactory $moduleFactory,
        PrestaLoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->context = $context;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->subscriptionOrderValidator = $subscriptionOrderValidator;
        $this->moduleFactory = $moduleFactory;
        $this->logger = $logger;
    }

    /** The merchant switched the component on and the shop can talk to Mollie in live mode. */
    public function isEnabled(): bool
    {
        if (!(int) $this->configuration->get(Config::MOLLIE_EXPRESS_COMPONENT_ENABLED)) {
            return $this->unavailable('disabled in settings');
        }

        if (Config::ENVIRONMENT_LIVE !== (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT)) {
            return $this->unavailable('environment is not live');
        }

        if (0 === strpos((string) $this->configuration->get(Config::MOLLIE_API_KEY), 'test')) {
            return $this->unavailable('live environment uses a test API key');
        }

        $module = $this->moduleFactory->getModule();

        if (!$module || !$module->getApiClient()) {
            return $this->unavailable('API client is not available');
        }

        return true;
    }

    /** The cart on the payment step has everything a session needs: customer, addresses, carrier, total. */
    public function isAvailable(\Cart $cart): bool
    {
        $cartId = (int) $cart->id;

        if (isset($this->cartAvailability[$cartId])) {
            return $this->cartAvailability[$cartId];
        }

        $available = $this->checkCart($cart);

        if (!$available) {
            $this->logger->debug(sprintf('%s - Express Component not shown: %s', self::FILE_NAME, $this->unavailableReason), [
                'cart_id' => $cartId,
            ]);
        }

        return $this->cartAvailability[$cartId] = $available;
    }

    private function checkCart(\Cart $cart): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$cart->id || !(int) $cart->id_customer) {
            return $this->unavailable('cart has no customer');
        }

        if (!(int) $cart->id_address_delivery || !(int) $cart->id_address_invoice) {
            return $this->unavailable('cart has no addresses');
        }

        if (!(int) $cart->id_carrier) {
            return $this->unavailable('cart has no carrier');
        }

        if ($this->subscriptionOrderValidator->validate($cart)) {
            return $this->unavailable('cart contains a subscription product');
        }

        if ($cart->getOrderTotal(true, \Cart::BOTH) <= 0) {
            return $this->unavailable('cart total is zero');
        }

        return true;
    }

    public function getUnavailableReason(): string
    {
        return $this->unavailableReason;
    }

    /**
     * @return array<string, string> button => 'automatic'|'hidden'
     */
    public function getButtonVisibility(): array
    {
        $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
        $shopId = $this->context->getShopId();
        $visibility = [];

        foreach (self::BUTTONS as $button => list($flag, $methodId)) {
            $visible = (bool) (int) $this->configuration->get($flag)
                && $this->isMethodEnabled($methodId, $environment, $shopId);

            $visibility[$button] = $visible ? self::VISIBILITY_AUTOMATIC : self::VISIBILITY_HIDDEN;
        }

        return $visibility;
    }

    private function isMethodEnabled(string $methodId, int $environment, int $shopId): bool
    {
        $paymentMethodId = (int) $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

        if (!$paymentMethodId) {
            return false;
        }

        /** @var \MolPaymentMethod|null $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['id_payment_method' => $paymentMethodId]);

        return $paymentMethod && (bool) $paymentMethod->enabled;
    }

    private function unavailable(string $reason): bool
    {
        $this->unavailableReason = $reason;

        return false;
    }
}
