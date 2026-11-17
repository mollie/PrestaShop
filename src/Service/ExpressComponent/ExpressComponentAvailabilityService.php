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
use Mollie\Repository\ModuleRestrictionRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
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

    /** Address fields the wallet sheet hands over in collect mode. */
    const COLLECTED_ADDRESS_FIELDS = ['firstname', 'lastname', 'address1', 'city', 'postcode', 'id_country', 'Country:name', 'id_state', 'State:name'];

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
    /** @var PaymentMethodRestrictionValidationInterface */
    private $restrictionValidation;
    /** @var SubscriptionOrderValidator */
    private $subscriptionOrderValidator;
    /** @var ModuleFactory */
    private $moduleFactory;
    /** @var PrestaLoggerInterface */
    private $logger;
    /** @var MinimumPurchaseService */
    private $minimumPurchaseService;
    /** @var ModuleRestrictionRepositoryInterface */
    private $moduleRestrictionRepository;
    /** @var string */
    private $unavailableReason = '';
    /** @var array<string, bool> hookDisplayHeader and hookDisplayPaymentTop ask for the same cart in one request */
    private $cartAvailability = [];

    public function __construct(
        ConfigurationAdapter $configuration,
        Context $context,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodRestrictionValidationInterface $restrictionValidation,
        SubscriptionOrderValidator $subscriptionOrderValidator,
        ModuleFactory $moduleFactory,
        PrestaLoggerInterface $logger,
        MinimumPurchaseService $minimumPurchaseService,
        ModuleRestrictionRepositoryInterface $moduleRestrictionRepository
    ) {
        $this->configuration = $configuration;
        $this->context = $context;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->restrictionValidation = $restrictionValidation;
        $this->subscriptionOrderValidator = $subscriptionOrderValidator;
        $this->moduleFactory = $moduleFactory;
        $this->logger = $logger;
        $this->minimumPurchaseService = $minimumPurchaseService;
        $this->moduleRestrictionRepository = $moduleRestrictionRepository;
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

    /** A switch the merchant never saved keeps the payment step on, as before placements existed. */
    public function isPlacementEnabled(string $placement): bool
    {
        if (!isset(Config::EXPRESS_PLACEMENT_FLAGS[$placement])) {
            return false;
        }

        $value = $this->getPlacementSetting(Config::EXPRESS_PLACEMENT_FLAGS[$placement]);

        if (false === $value || null === $value) {
            return Config::EXPRESS_PLACEMENT_PAYMENT === $placement;
        }

        return (bool) (int) $value;
    }

    /** The cart on the payment step has everything a session needs: customer, addresses, carrier, total. */
    public function isAvailable(\Cart $cart): bool
    {
        return $this->rememberAvailability($cart, false);
    }

    /**
     * Mollie's wallet sheet collects the email, the addresses and the carrier, so the cart only
     * needs products and a visitor who may become a customer.
     */
    public function isAvailableForCollect(\Cart $cart): bool
    {
        return $this->rememberAvailability($cart, true);
    }

    private function rememberAvailability(\Cart $cart, bool $collectDetails): bool
    {
        $cartId = (int) $cart->id;
        $key = ($collectDetails ? 'collect-' : 'payment-') . $cartId;

        if (isset($this->cartAvailability[$key])) {
            return $this->cartAvailability[$key];
        }

        $available = $collectDetails ? $this->checkCollectCart($cart) : $this->checkCart($cart);

        if (!$available) {
            $this->logger->debug(sprintf('%s - Express Component not shown: %s', self::FILE_NAME, $this->unavailableReason), [
                'cart_id' => $cartId,
                'collect_details' => $collectDetails,
            ]);
        }

        return $this->cartAvailability[$key] = $available;
    }

    private function checkCollectCart(\Cart $cart): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$cart->id || !$cart->hasProducts()) {
            return $this->unavailable('cart has no products');
        }

        if (!(int) $cart->id_customer && !(int) $this->configuration->get('PS_GUEST_CHECKOUT_ENABLED')) {
            return $this->unavailable('visitor cannot become a customer: guest checkout is disabled');
        }

        $missingFields = array_values(array_diff($this->getRequiredAddressFields(), self::COLLECTED_ADDRESS_FIELDS));

        if ($missingFields) {
            return $this->unavailable(sprintf('shop requires address fields the wallet cannot supply: %s', implode(', ', $missingFields)));
        }

        if (!$this->checkModuleRestrictions($cart)) {
            return false;
        }

        return $this->checkContents($cart, \Cart::BOTH_WITHOUT_SHIPPING);
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

        // Downloads and other virtual products ship nothing, so such a cart never gets a carrier.
        $isVirtualCart = (bool) $cart->isVirtualCart();

        // PrestaShop falls back to its default carrier when the cart holds no choice yet (the page
        // shows it and the order gets it), so only a cart no carrier can serve is refused. A stale
        // id_carrier is no proof either: it survives an address no carrier serves.
        $deliveryOption = $isVirtualCart ? [] : $cart->getDeliveryOption(null, false, false);

        if (!$isVirtualCart && !$deliveryOption) {
            return $this->unavailable('no carrier serves the delivery address');
        }

        if (!$this->checkModuleRestrictions($cart)) {
            return false;
        }

        $moduleId = $this->getModuleId();
        $shopId = $this->context->getShopId();

        if (!$this->moduleRestrictionRepository->isCountryAllowed($moduleId, $shopId, $this->getAddressCountryId((int) $cart->id_address_delivery))) {
            return $this->unavailable('delivery country not allowed for the module');
        }

        foreach ($this->getCarrierIds($deliveryOption) as $carrierId) {
            if (!$this->moduleRestrictionRepository->isCarrierReferenceAllowed($moduleId, $shopId, $this->getCarrierReference($carrierId))) {
                return $this->unavailable('carrier not allowed for the module');
            }
        }

        return $this->checkContents($cart, \Cart::BOTH);
    }

    /**
     * The currency and customer group restrictions of Payment > Preferences, which PrestaShop
     * applies to the regular payment options.
     */
    private function checkModuleRestrictions(\Cart $cart): bool
    {
        $moduleId = $this->getModuleId();
        $shopId = $this->context->getShopId();

        if (!$this->moduleRestrictionRepository->isCurrencyAllowed($moduleId, $shopId, (int) $cart->id_currency)) {
            return $this->unavailable('currency not allowed for the module');
        }

        if (!(int) $this->configuration->get('PS_GROUP_FEATURE_ACTIVE')) {
            return true;
        }

        if (!$this->moduleRestrictionRepository->isAnyGroupAllowed($moduleId, $shopId, $this->getVisitorGroupIds((int) $cart->id_customer))) {
            return $this->unavailable('customer group not allowed for the module');
        }

        return true;
    }

    /** isEnabled() has already made sure the module loads. */
    private function getModuleId(): int
    {
        return (int) $this->moduleFactory->getModule()->id;
    }

    /**
     * @return int[]
     */
    private function getVisitorGroupIds(int $customerId): array
    {
        // a cart without a customer is placed by a guest, so it is judged as one
        if (!$customerId) {
            return [(int) $this->configuration->get('PS_GUEST_GROUP')];
        }

        return $this->getCustomerGroupIds($customerId);
    }

    /**
     * @param array<int, string> $deliveryOption address id => "carrier id,carrier id,"
     *
     * @return int[]
     */
    private function getCarrierIds(array $deliveryOption): array
    {
        $carrierIds = [];

        foreach ($deliveryOption as $carrierList) {
            foreach (explode(',', (string) $carrierList) as $carrierId) {
                if ((int) $carrierId) {
                    $carrierIds[] = (int) $carrierId;
                }
            }
        }

        return array_values(array_unique($carrierIds));
    }

    /**
     * @param int $totalType the total the session is created for: without shipping when Mollie collects it
     */
    private function checkContents(\Cart $cart, int $totalType): bool
    {
        if ($this->subscriptionOrderValidator->validate($cart)) {
            return $this->unavailable('cart contains a subscription product');
        }

        if ($cart->getOrderTotal(true, $totalType) <= 0) {
            return $this->unavailable('cart total is zero');
        }

        if ($this->minimumPurchaseService->isBelowMinimum($cart)) {
            return $this->unavailable('cart is below the minimum purchase total');
        }

        // Mollie mounts its iframe even when every button is hidden, so the shopper would see an
        // empty "Express checkout" block.
        if (!in_array(self::VISIBILITY_AUTOMATIC, $this->getButtonVisibility(), true)) {
            return $this->unavailable('no express button is offered for this cart');
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
                && $this->isMethodOffered($methodId, $environment, $shopId);

            $visibility[$button] = $visible ? self::VISIBILITY_AUTOMATIC : self::VISIBILITY_HIDDEN;
        }

        return $visibility;
    }

    /**
     * The button follows the method it settles through: the row must be enabled and pass the
     * same restrictions as the regular option (amount, customer group, country, vouchers), so a
     * wallet the merchant restricted is not offered through the back door of the express strip.
     */
    private function isMethodOffered(string $methodId, int $environment, int $shopId): bool
    {
        $paymentMethodId = (int) $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

        if (!$paymentMethodId) {
            return false;
        }

        /** @var \MolPaymentMethod|null $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['id_payment_method' => $paymentMethodId]);

        // Whether this device can pay with Apple Pay is Mollie's call (visibility "automatic"), as
        // in Mollie's other plugins. The regular option keeps its isApplePayMethod cookie check.
        $skippedValidators = 'applepay' === $methodId ? [ApplePayPaymentMethodRestrictionValidator::class] : [];

        return $paymentMethod
            && (bool) $paymentMethod->enabled
            && $this->restrictionValidation->isPaymentMethodValid($paymentMethod, $skippedValidators);
    }

    /**
     * Read raw: the adapter returns null for a saved "0" as well, and an unsaved switch has its own default.
     *
     * @return string|false|null
     */
    protected function getPlacementSetting(string $key)
    {
        return \Configuration::get($key, null, $this->context->getShopGroupId(), $this->context->getShopId());
    }

    /**
     * Address fields the merchant made required in the back office plus the ones every address format needs.
     *
     * @return string[]
     */
    protected function getRequiredAddressFields(): array
    {
        return \AddressFormat::getFieldsRequired();
    }

    /**
     * @return int[]
     */
    protected function getCustomerGroupIds(int $customerId): array
    {
        return array_map('intval', \Customer::getGroupsStatic($customerId));
    }

    protected function getAddressCountryId(int $addressId): int
    {
        $countryAndState = \Address::getCountryAndState($addressId);

        return $countryAndState ? (int) $countryAndState['id_country'] : 0;
    }

    protected function getCarrierReference(int $carrierId): int
    {
        return (int) (new \Carrier($carrierId))->id_reference;
    }

    private function unavailable(string $reason): bool
    {
        $this->unavailableReason = $reason;

        return false;
    }
}
