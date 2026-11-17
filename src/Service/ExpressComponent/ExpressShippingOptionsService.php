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

use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Exception\ExpressShippingRejectedException;
use Mollie\Factory\ModuleFactory;
use Mollie\Repository\ModuleRestrictionRepositoryInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Utility\ApplePayDirect\DeliveryOptionUtility;
use Mollie\Utility\PostcodeUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Answers Mollie's shipping callback for a collect mode Express session: puts the partial address
 * the wallet sent on the cart as a soft-deleted placeholder and lists the carriers that can ship
 * there. The session amount holds the products only and Mollie adds the picked option on top, so
 * each option is quoted as what the cart total with that carrier adds to the session amount.
 */
class ExpressShippingOptionsService
{
    const PLACEHOLDER_NAME = 'Express';
    // Apple Pay hands out only the first part of the postcode before payment in these countries.
    const SHORTENED_POSTCODE_COUNTRIES = ['GB', 'CA'];

    /** @var MolExpressSessionRepositoryInterface */
    private $sessionRepository;
    /** @var MinimumPurchaseService */
    private $minimumPurchaseService;
    /** @var ExpressComponentAvailabilityService */
    private $availability;
    /** @var Context */
    private $context;
    /** @var ModuleRestrictionRepositoryInterface */
    private $moduleRestrictionRepository;
    /** @var ModuleFactory */
    private $moduleFactory;
    /** @var ExpressQuoteCustomerService */
    private $quoteCustomerService;

    public function __construct(
        MolExpressSessionRepositoryInterface $sessionRepository,
        MinimumPurchaseService $minimumPurchaseService,
        ExpressComponentAvailabilityService $availability,
        Context $context,
        ModuleRestrictionRepositoryInterface $moduleRestrictionRepository,
        ModuleFactory $moduleFactory,
        ExpressQuoteCustomerService $quoteCustomerService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->minimumPurchaseService = $minimumPurchaseService;
        $this->availability = $availability;
        $this->context = $context;
        $this->moduleRestrictionRepository = $moduleRestrictionRepository;
        $this->moduleFactory = $moduleFactory;
        $this->quoteCustomerService = $quoteCustomerService;
    }

    /**
     * @param array{postalCode?: string, city?: string, region?: string, country?: string} $shippingAddress
     *
     * @return array<int, array{reference: string, description: string, amount: array{currency: string, value: string}}>
     *
     * @throws ExpressShippingRejectedException
     */
    public function getOptions(\Cart $cart, array $shippingAddress): array
    {
        $session = $this->sessionRepository->findLatestByCartId((int) $cart->id);

        if (!$session) {
            throw ExpressShippingRejectedException::unsupportedAddress(sprintf('No Express session is stored for cart %d', (int) $cart->id));
        }

        $sessionAmount = (float) $session->amount;
        $countryIso = \Tools::strtoupper(trim((string) ($shippingAddress['country'] ?? '')));
        $country = $this->findCountry($countryIso);

        if (!$country || !$country->active) {
            throw ExpressShippingRejectedException::unsupportedCountry(sprintf('Country "%s" does not exist or is not active', $countryIso));
        }

        $countryId = (int) $country->id;

        if (!$this->moduleRestrictionRepository->isCountryAllowed($this->getModuleId(), $this->context->getShopId(), $countryId)) {
            throw ExpressShippingRejectedException::unsupportedCountry(sprintf('Country "%s" is not allowed for the module in Payment > Preferences', $countryIso));
        }

        if ($this->needsDni($countryId)) {
            throw ExpressShippingRejectedException::unsupportedCountry(sprintf('Country "%s" requires an identification number the wallet cannot supply', $countryIso));
        }

        $stateId = 0;

        if ($country->contains_states) {
            $region = trim((string) ($shippingAddress['region'] ?? ''));
            $stateId = $this->findStateId($region, $countryId);

            if (!$stateId) {
                throw ExpressShippingRejectedException::unsupportedAddress(sprintf('Region "%s" matches no state of country "%s"', $region, $countryIso));
            }
        }

        $shippingAddress['postalCode'] = $this->getPostcodeInShopFormat($country, $countryIso, trim((string) ($shippingAddress['postalCode'] ?? '')));

        $addressId = $this->applyPlaceholderAddress($cart, $countryId, $stateId, $shippingAddress);

        $productTotal = $this->formatAmount($cart->getOrderTotal(true, \Cart::BOTH_WITHOUT_SHIPPING));

        if ($productTotal !== $this->formatAmount($sessionAmount)) {
            throw ExpressShippingRejectedException::unsupportedCountry(sprintf('The product total for this address (%s) differs from the session amount (%s)', $productTotal, $this->formatAmount($sessionAmount)));
        }

        $minimumPurchaseMessage = $this->minimumPurchaseService->getNotReachedMessage($cart);

        if ($minimumPurchaseMessage) {
            throw ExpressShippingRejectedException::unsupportedAddress($minimumPurchaseMessage);
        }

        if (!in_array(ExpressComponentAvailabilityService::VISIBILITY_AUTOMATIC, $this->availability->getButtonVisibility(), true)) {
            // the payment methods also judge amount, currency and group here, not only the country
            throw ExpressShippingRejectedException::unsupportedAddress(sprintf('No express payment method is offered for this cart with country "%s"', $countryIso));
        }

        $options = $this->buildOptions($cart, $addressId, $sessionAmount, $this->context->getCurrencyIso());

        if (!$options) {
            throw ExpressShippingRejectedException::unsupportedAddress(sprintf('No carrier ships cart %d to this address for the session amount', (int) $cart->id));
        }

        return $options;
    }

    /**
     * Core reads the carrier groups from the cart's customer. An anonymous cart is quoted for the
     * hidden guest of ExpressQuoteCustomerService, on the cart object only: the cart row keeps no
     * customer, so the shopper's next page load sees the cart as before.
     */
    private function buildOptions(\Cart $cart, int $addressId, float $sessionAmount, string $currencyIso): array
    {
        $originalDeliveryOption = $cart->delivery_option;
        $originalCustomerId = (int) $cart->id_customer;
        $options = [];

        $cart->id_customer = $originalCustomerId ?: $this->quoteCustomerService->getCustomerId();
        $this->cleanCarrierCache();

        try {
            foreach ($this->getAllowedCarriers($this->getUsableCarriers($cart, $addressId)) as $carrier) {
                // raw assignment on purpose: Cart::setDeliveryOption() fires CartRule::autoRemoveFromCart(),
                // which deletes vouchers from the shopper's cart while this loop is only quoting
                $cart->delivery_option = json_encode([$addressId => (int) $carrier['id_carrier'] . ',']);

                $optionAmount = $this->getOptionAmount((float) $cart->getOrderTotal(true, \Cart::BOTH), $sessionAmount);

                if ($optionAmount < 0) {
                    continue;
                }

                $options[] = $this->buildOption($carrier, $optionAmount, $currencyIso);
            }
        } finally {
            $cart->delivery_option = $originalDeliveryOption;
            $cart->id_customer = $originalCustomerId;
            $this->cleanCarrierCache();
        }

        return $options;
    }

    /**
     * Carrier::getAvailableCarrierList() caches the carriers per zone and cart, not per customer.
     */
    protected function cleanCarrierCache(): void
    {
        \Cache::clean('Carrier::getAvailableCarrierList_getCarriersForOrder_*');
    }

    /**
     * Carriers the merchant did not allow for the module in Payment > Preferences are not offered.
     *
     * @param array $carriers rows with an id_reference
     */
    private function getAllowedCarriers(array $carriers): array
    {
        $moduleId = $this->getModuleId();
        $shopId = $this->context->getShopId();

        return array_filter($carriers, function (array $carrier) use ($moduleId, $shopId) {
            return $this->moduleRestrictionRepository->isCarrierReferenceAllowed($moduleId, $shopId, (int) $carrier['id_reference']);
        });
    }

    private function getModuleId(): int
    {
        $module = $this->moduleFactory->getModule();

        return $module ? (int) $module->id : 0;
    }

    /**
     * Mollie charges the session amount plus the option amount, so the option is quoted as the
     * difference to the cart total with that carrier, not as the carrier's shipping price: a free
     * shipping cart rule then quotes 0.00 and the charge always equals the order the shop creates.
     * A negative difference (a carrier bound discount larger than its shipping) cannot be quoted.
     */
    private function getOptionAmount(float $totalWithCarrier, float $sessionAmount): float
    {
        // abs() turns a rounded -0.0 into 0.0, which PHP 7 would format as "-0.00"
        $amount = round($totalWithCarrier - $sessionAmount, 2);

        return $amount < 0 ? $amount : abs($amount);
    }

    private function buildOption(array $carrier, float $optionAmount, string $currencyIso): array
    {
        $description = (string) $carrier['name'];
        $delay = trim((string) ($carrier['delay'] ?? ''));

        if ('' !== $delay) {
            $description .= sprintf(' (%s)', $delay);
        }

        return [
            'reference' => (string) $carrier['id_reference'],
            'description' => $description,
            'amount' => [
                'currency' => $currencyIso,
                'value' => $this->formatAmount($optionAmount),
            ],
        ];
    }

    /**
     * The sheet refuses the address now, while the shopper can still correct it. After payment a
     * postcode the shop format refuses would leave a paid payment without an order.
     *
     * @throws ExpressShippingRejectedException
     */
    private function getPostcodeInShopFormat(\Country $country, string $countryIso, string $postcode): string
    {
        if (!$country->need_zip_code || '' === $postcode || in_array($countryIso, self::SHORTENED_POSTCODE_COUNTRIES, true)) {
            return $postcode;
        }

        $valid = PostcodeUtility::findValid($country, $postcode, $countryIso);

        if (null === $valid) {
            throw ExpressShippingRejectedException::unsupportedPostalCode(sprintf('Postcode "%s" does not match the format of country "%s"', $postcode, $countryIso));
        }

        return $valid;
    }

    private function formatAmount(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }

    /**
     * @return \Country|null
     */
    protected function findCountry(string $iso)
    {
        // Country::getByIso() throws on a malformed code, and the wallet may send none at all
        if (!\Validate::isLanguageIsoCode($iso)) {
            return null;
        }

        $countryId = (int) \Country::getByIso($iso);

        if (!$countryId) {
            return null;
        }

        return new \Country($countryId);
    }

    protected function needsDni(int $countryId): bool
    {
        return (bool) \Country::isNeedDniByCountryId($countryId);
    }

    /**
     * Wallets send the region as an ISO code (Apple Pay, Google Pay) or as a name.
     * State::getIdByName() is not scoped to a country, so its match is checked.
     */
    protected function findStateId(string $region, int $countryId): int
    {
        if ('' === $region) {
            return 0;
        }

        $stateId = (int) \State::getIdByIso($region, $countryId);

        if ($stateId) {
            return $stateId;
        }

        $stateId = (int) \State::getIdByName($region);

        if (!$stateId || (int) (new \State($stateId))->id_country !== $countryId) {
            return 0;
        }

        return $stateId;
    }

    /**
     * Same technique as UpdateApplePayShippingContactHandler: the soft-deleted placeholder stays out
     * of the customer's address book, and the cart lines follow it because PS 1.7 and 8 key the
     * delivery options by each line's address.
     */
    protected function applyPlaceholderAddress(\Cart $cart, int $countryId, int $stateId, array $shippingAddress): int
    {
        $address = $this->getPlaceholderAddress((int) $cart->id_address_delivery);
        $city = trim((string) ($shippingAddress['city'] ?? ''));
        // the wallet may truncate the postal code; it is stored as far as Address accepts it, never validated
        $postcode = trim((string) preg_replace('/[^a-zA-Z 0-9-]/', '', (string) ($shippingAddress['postalCode'] ?? '')));

        $address->id_country = $countryId;
        $address->id_state = $stateId;
        $address->city = '' !== $city && \Validate::isCityName($city) ? \Tools::substr($city, 0, 64) : self::PLACEHOLDER_NAME;
        $address->postcode = \Tools::substr($postcode, 0, 12);
        $address->id_customer = (int) $cart->id_customer;
        $address->deleted = true;
        $address->save();

        // a reused placeholder may already be cached with the previous country, which would keep
        // that country's VAT and shipping tax in the totals below
        \Cache::clean('Address::initialize_' . (int) $address->id);

        $addressId = (int) $address->id;
        $invoiceAddressId = (int) $cart->id_address_invoice;

        $cart->id_address_delivery = $addressId;

        if (!$invoiceAddressId || $this->isPlaceholderAddress($invoiceAddressId)) {
            $cart->id_address_invoice = $addressId;
        }

        $cart->update();

        foreach (array_unique(array_column($cart->getProducts(true), 'id_address_delivery')) as $lineAddressId) {
            if ((int) $lineAddressId === $addressId) {
                continue;
            }

            $cart->updateAddressId((int) $lineAddressId, $addressId);
        }

        \Context::getContext()->country = new \Country($countryId, (int) $cart->id_lang);

        return $addressId;
    }

    /**
     * The zone comes from the address, so carriers of a state with its own zone are found.
     *
     * @return array rows from Carrier::getCarriersForOrder() that the cart's delivery option list offers
     */
    protected function getUsableCarriers(\Cart $cart, int $addressId): array
    {
        $zoneId = (int) \Address::getZoneById($addressId);

        return DeliveryOptionUtility::filterUsableCarriers(
            \Carrier::getCarriersForOrder($zoneId, \Customer::getGroupsStatic((int) $cart->id_customer), $cart),
            $cart->getDeliveryOptionList(null, true),
            $addressId
        );
    }

    private function getPlaceholderAddress(int $deliveryAddressId): \Address
    {
        if ($deliveryAddressId && $this->isPlaceholderAddress($deliveryAddressId)) {
            return new \Address($deliveryAddressId);
        }

        $address = new \Address();
        $address->alias = Config::EXPRESS_ADDRESS_ALIAS;
        $address->firstname = self::PLACEHOLDER_NAME;
        $address->lastname = self::PLACEHOLDER_NAME;
        $address->address1 = self::PLACEHOLDER_NAME;

        return $address;
    }

    private function isPlaceholderAddress(int $addressId): bool
    {
        $address = new \Address($addressId);

        return \Validate::isLoadedObject($address) && Config::EXPRESS_ADDRESS_ALIAS === $address->alias;
    }
}
