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
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\ExpressCustomerDetailsException;
use Mollie\Exception\GuestCheckoutNotAvailableException;
use Mollie\Exception\RetryOverException;
use Mollie\Handler\RetryHandlerInterface;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Utility\ExpressAddressUtility;
use Mollie\Utility\ExpressPaymentUtility;
use Mollie\Utility\PostcodeUtility;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * A collect session lets Mollie's wallet sheet gather the email, addresses and shipping option of
 * a shopper the shop may not know yet. Right before the order is created, this puts them on the
 * cart: a guest when the cart has no customer, the two addresses and the chosen carrier.
 */
class ExpressCustomerDetailsService
{
    const FILE_NAME = 'ExpressCustomerDetailsService';

    /** Mollie can fill the addresses a moment after the webhook fires, and gives the webhook 15 seconds. */
    const DETAILS_MAX_ATTEMPTS = 3;

    const ADDRESS_FIELDS = ['firstname', 'lastname', 'address1', 'address2', 'city', 'postcode'];
    const CUSTOMER_FIELDS = ['firstname', 'lastname', 'email'];

    /** @var MolExpressSessionRepositoryInterface */
    private $sessionRepository;
    /** @var MollieSessionApiAdapterInterface */
    private $sessionApi;
    /** @var RetryHandlerInterface */
    private $retryHandler;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(
        MolExpressSessionRepositoryInterface $sessionRepository,
        MollieSessionApiAdapterInterface $sessionApi,
        RetryHandlerInterface $retryHandler,
        ConfigurationAdapter $configuration,
        PrestaLoggerInterface $logger
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->sessionApi = $sessionApi;
        $this->retryHandler = $retryHandler;
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @param object $apiPayment Mollie payment of a collect session
     *
     * @throws ExpressCustomerDetailsException
     * @throws GuestCheckoutNotAvailableException
     */
    public function applyToCart(\Cart $cart, $apiPayment): void
    {
        $cartId = (int) $cart->id;
        $isVirtual = (bool) $cart->isVirtualCart();
        $details = $this->resolveDetails($cartId, $apiPayment, $isVirtual);

        $billing = $details['billing'];
        $shipping = $isVirtual ? $billing : $details['shipping'];
        $invoice = $this->text($billing, 'streetAndNumber') === '' ? $shipping : $billing;

        $carrierId = $isVirtual ? 0 : $this->resolveCarrierId($cartId, $details['reference']);

        $customerFields = $this->buildCustomerFields($billing, $shipping);
        $customer = $this->resolveCustomer($cart, $customerFields);
        $customerId = (int) $customer->id;

        $deliveryAddressId = $this->getOrCreateAddress((int) $cart->id_address_delivery, $customerId, $cartId, $this->buildAddressFields($shipping, $customerFields));
        $invoiceAddressId = $invoice === $shipping
            ? $deliveryAddressId
            : $this->getOrCreateAddress((int) $cart->id_address_invoice, $customerId, $cartId, $this->buildAddressFields($invoice, $customerFields));

        ExpressAddressUtility::moveCartLines($cart, $deliveryAddressId);

        $cart->id_customer = $customerId;
        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->update();
        $cart->setTaxCalculationMethod();

        // The delivery option list is built from the context customer and the cart addresses.
        $this->refreshContext($cart, $customer);

        if ($carrierId) {
            $cart->id_carrier = $carrierId;
            $cart->setDeliveryOption([$deliveryAddressId => $carrierId . ',']);
            $cart->update();
        }

        $this->logger->debug(sprintf('%s - Express customer details applied to the cart', self::FILE_NAME), [
            'cart_id' => $cartId,
            'customer_id' => $customerId,
            'id_address_delivery' => $deliveryAddressId,
            'id_address_invoice' => $invoiceAddressId,
            'id_carrier' => $carrierId,
        ]);
    }

    /**
     * Hides the wallet addresses from the customer's address book once the order holds them.
     */
    public function hideOrderAddresses(\Order $order): void
    {
        foreach (array_unique([(int) $order->id_address_delivery, (int) $order->id_address_invoice]) as $addressId) {
            $address = new \Address($addressId);

            if (!\Validate::isLoadedObject($address) || Config::EXPRESS_ADDRESS_ALIAS !== $address->alias) {
                continue;
            }

            $address->deleted = true;
            $address->update();
        }
    }

    /**
     * @return array{firstname: string, lastname: string, email: string}
     */
    public function buildCustomerFields(array $billing, array $shipping): array
    {
        return [
            'firstname' => $this->text($billing, 'givenName') ?: $this->text($shipping, 'givenName'),
            'lastname' => $this->text($billing, 'familyName') ?: $this->text($shipping, 'familyName'),
            'email' => $this->text($billing, 'email') ?: $this->text($shipping, 'email'),
        ];
    }

    /**
     * @param array $mollieAddress Mollie address, empty fields omitted
     * @param array $customerFields names used when the wallet sent an address without them
     */
    public function buildAddressFields(array $mollieAddress, array $customerFields): array
    {
        return [
            'firstname' => $this->text($mollieAddress, 'givenName') ?: (string) ($customerFields['firstname'] ?? ''),
            'lastname' => $this->text($mollieAddress, 'familyName') ?: (string) ($customerFields['lastname'] ?? ''),
            'address1' => $this->text($mollieAddress, 'streetAndNumber'),
            'address2' => $this->text($mollieAddress, 'streetAdditional'),
            'city' => $this->text($mollieAddress, 'city'),
            'postcode' => $this->text($mollieAddress, 'postalCode'),
            'country' => strtoupper($this->text($mollieAddress, 'country')),
            'region' => $this->text($mollieAddress, 'region'),
            'phone' => $this->text($mollieAddress, 'phone'),
        ];
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    private function findValidPostcode(\Country $country, string $postcode, string $countryIso): string
    {
        $valid = PostcodeUtility::findValid($country, $postcode, $countryIso);

        if (null !== $valid) {
            return $valid;
        }

        throw ExpressCustomerDetailsException::invalidField('address', 'postcode', sprintf('"%s" does not match the format of country %s', $postcode, $countryIso));
    }

    /**
     * @return array{billing: array, shipping: array, reference: string}
     *
     * @throws ExpressCustomerDetailsException
     */
    private function resolveDetails(int $cartId, $apiPayment, bool $isVirtual): array
    {
        $details = [
            'billing' => ExpressPaymentUtility::toArray($apiPayment->billingAddress ?? null),
            'shipping' => ExpressPaymentUtility::toArray($apiPayment->shippingAddress ?? null),
            'reference' => ExpressPaymentUtility::findShippingReference($apiPayment->lines ?? null),
        ];

        if ($this->isComplete($details, $isVirtual)) {
            return $details;
        }

        $stored = $this->sessionRepository->findLatestByCartId($cartId);

        if (!$stored || empty($stored->session_id)) {
            throw ExpressCustomerDetailsException::detailsMissing($cartId);
        }

        $sessionId = (string) $stored->session_id;

        try {
            $details = $this->retryHandler->retry(
                function () use ($details, $sessionId, $cartId, $isVirtual) {
                    $session = $this->sessionApi->get($sessionId);

                    $merged = [
                        'billing' => $details['billing'] ?: (array) $session->getBillingAddress(),
                        'shipping' => $details['shipping'] ?: (array) $session->getShippingAddress(),
                        'reference' => $details['reference'] ?: (string) $session->getShippingReference(),
                    ];

                    if (!$this->hasAddresses($merged, $isVirtual)) {
                        throw ExpressCustomerDetailsException::detailsMissing($cartId);
                    }

                    return $merged;
                },
                [
                    'max' => self::DETAILS_MAX_ATTEMPTS,
                    'accepted_exception' => ExpressCustomerDetailsException::class,
                ]
            );
        } catch (RetryOverException $exception) {
            throw ExpressCustomerDetailsException::detailsMissing($cartId);
        }

        return $details;
    }

    private function isComplete(array $details, bool $isVirtual): bool
    {
        return $this->hasAddresses($details, $isVirtual) && ($isVirtual || '' !== $details['reference']);
    }

    private function hasAddresses(array $details, bool $isVirtual): bool
    {
        return !empty($details['billing']) && ($isVirtual || !empty($details['shipping']));
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    private function resolveCarrierId(int $cartId, string $reference): int
    {
        if ('' === $reference) {
            throw ExpressCustomerDetailsException::shippingOptionMissing($cartId);
        }

        $carrierId = $this->findCarrierIdByReference($reference);

        if (!$carrierId) {
            throw ExpressCustomerDetailsException::carrierNotFound($reference);
        }

        return $carrierId;
    }

    /**
     * @throws GuestCheckoutNotAvailableException
     * @throws ExpressCustomerDetailsException
     */
    private function resolveCustomer(\Cart $cart, array $customerFields): \Customer
    {
        if ((int) $cart->id_customer > 0) {
            return $this->loadCustomer((int) $cart->id_customer);
        }

        if (!$this->configuration->get('PS_GUEST_CHECKOUT_ENABLED', (int) $cart->id_shop)) {
            throw GuestCheckoutNotAvailableException::guestCheckoutDisabled();
        }

        return $this->createGuestCustomer($customerFields, $cart);
    }

    /**
     * A webhook retried after a failed order creation finds the addresses of the first attempt.
     */
    private function getOrCreateAddress(int $currentAddressId, int $customerId, int $cartId, array $fields): int
    {
        if ($this->isReusableAddress($currentAddressId, $customerId)) {
            return $currentAddressId;
        }

        return $this->createAddress($fields, $customerId, $cartId);
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    protected function loadCustomer(int $customerId): \Customer
    {
        $customer = new \Customer($customerId);

        if (!\Validate::isLoadedObject($customer)) {
            throw ExpressCustomerDetailsException::invalidField('cart', 'id_customer', sprintf('customer %d does not exist', $customerId));
        }

        return $customer;
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    protected function createGuestCustomer(array $fields, \Cart $cart): \Customer
    {
        $customer = new \Customer();
        $customer->is_guest = true;
        $customer->firstname = $fields['firstname'];
        $customer->lastname = $fields['lastname'];
        $customer->email = $fields['email'];
        $customer->passwd = (new Hashing())->hash(bin2hex(random_bytes(16)), _COOKIE_KEY_);
        $customer->id_shop = (int) $cart->id_shop;
        $customer->id_shop_group = (int) $cart->id_shop_group;
        $customer->id_lang = (int) $cart->id_lang;

        $this->assertValidFields($customer, self::CUSTOMER_FIELDS, 'customer');

        if (!$customer->add()) {
            throw ExpressCustomerDetailsException::notSaved('customer', (int) $cart->id);
        }

        // Customer::add() generates a new key; the webhook token is a hash of the cart's one.
        $customer->secure_key = (string) $cart->secure_key;
        $customer->update();

        return $customer;
    }

    protected function isReusableAddress(int $addressId, int $customerId): bool
    {
        if ($addressId <= 0 || $customerId <= 0) {
            return false;
        }

        $address = new \Address($addressId);

        return \Validate::isLoadedObject($address)
            && !$address->deleted
            && Config::EXPRESS_ADDRESS_ALIAS === $address->alias
            && (int) $address->id_customer === $customerId;
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    protected function createAddress(array $fields, int $customerId, int $cartId): int
    {
        $countryId = (int) \Country::getByIso($fields['country']);

        if (!$countryId) {
            throw ExpressCustomerDetailsException::invalidField('address', 'country', sprintf('unknown country "%s"', $fields['country']));
        }

        $country = new \Country($countryId);

        $address = new \Address();
        $address->alias = Config::EXPRESS_ADDRESS_ALIAS;
        $address->id_customer = $customerId;
        $address->id_country = $countryId;
        $address->id_state = ExpressAddressUtility::findStateId($countryId, $fields['region']);

        foreach (self::ADDRESS_FIELDS as $field) {
            $address->{$field} = $fields[$field];
        }

        if ($country->contains_states && !$address->id_state) {
            throw ExpressCustomerDetailsException::invalidField('address', 'region', sprintf('no state "%s" in country %s', $fields['region'], $fields['country']));
        }

        if ($country->need_zip_code) {
            $address->postcode = $this->findValidPostcode($country, $fields['postcode'], $fields['country']);
        }

        if ('' !== $fields['phone'] && true === $address->validateField('phone', $fields['phone'])) {
            $address->phone = $fields['phone'];
        }

        $this->assertValidFields($address, self::ADDRESS_FIELDS, 'address');

        if (!$address->add()) {
            throw ExpressCustomerDetailsException::notSaved('address', $cartId);
        }

        return (int) $address->id;
    }

    protected function findCarrierIdByReference(string $reference): int
    {
        if (!ctype_digit($reference)) {
            return 0;
        }

        $carrier = \Carrier::getCarrierByReference((int) $reference);

        return $carrier ? (int) $carrier->id : 0;
    }

    /**
     * Tax and validateOrder read the context the webhook set up for the still anonymous cart.
     */
    protected function refreshContext(\Cart $cart, \Customer $customer): void
    {
        $context = \Context::getContext();
        $context->customer = $customer;
        $context->cart = $cart;

        $deliveryAddress = new \Address((int) $cart->id_address_delivery);

        if (\Validate::isLoadedObject($deliveryAddress) && $deliveryAddress->id_country) {
            $context->country = new \Country((int) $deliveryAddress->id_country, (int) $cart->id_lang);
        }
    }

    /**
     * @throws ExpressCustomerDetailsException
     */
    private function assertValidFields(\ObjectModel $object, array $fields, string $entity): void
    {
        foreach ($fields as $field) {
            $result = $object->validateField($field, $object->{$field});

            if (true !== $result) {
                throw ExpressCustomerDetailsException::invalidField($entity, $field, (string) $result);
            }
        }
    }

    private function text(array $data, string $key): string
    {
        return isset($data[$key]) && is_scalar($data[$key]) ? trim((string) $data[$key]) : '';
    }
}
