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

use Exception;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\LegacyContext;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Config\Config;
use Mollie\Exception\MollieApiException;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProviderInterface;
use Mollie\Utility\NumberUtility;
use MolPaymentMethod;
use PrestaShopDatabaseException;
use PrestaShopException;
use Shop;

class ApiService implements ApiServiceInterface
{
    private $errors = [];

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @var PaymentMethodSortProviderInterface
     */
    private $paymentMethodSortProvider;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var int
     */
    private $environment;

    /*
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @var Shop
     */
    private $shop;
    /** @var TaxCalculatorProvider */
    private $taxCalculatorProvider;
    /** @var LegacyContext */
    private $context;

    public function __construct(
        PaymentMethodRepositoryInterface $methodRepository,
        CountryRepository $countryRepository,
        PaymentMethodSortProviderInterface $paymentMethodSortProvider,
        ConfigurationAdapter $configurationAdapter,
        TransactionService $transactionService,
        Shop $shop,
        TaxCalculatorProvider $taxCalculatorProvider,
        LegacyContext $context
    ) {
        $this->countryRepository = $countryRepository;
        $this->paymentMethodSortProvider = $paymentMethodSortProvider;
        $this->methodRepository = $methodRepository;
        $this->configurationAdapter = $configurationAdapter;
        $this->environment = (int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT);
        $this->transactionService = $transactionService;
        $this->shop = $shop;
        $this->taxCalculatorProvider = $taxCalculatorProvider;
        $this->context = $context;
    }

    /**
     * Get payment methods to show on the configuration page.
     *
     * @param MollieApiClient $api
     *
     * @return array
     *
     * @since 3.0.0
     * @since 3.4.0 public
     *
     * @public ✓ This method is part of the public API
     */
    public function getMethodsForConfig(MollieApiClient $api)
    {
        $notAvailable = [];
        try {
            /** @var BaseCollection|MethodCollection $apiMethods */
            $apiMethods = $api->methods->allActive(['resource' => 'orders', 'include' => 'issuers', 'includeWallets' => 'applepay']);
            $apiMethods = $apiMethods->getArrayCopy();
        } catch (Exception $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            $this->errors[] = $e->getMessage();

            return [];
        }

        if (!count($apiMethods)) {
            return [];
        }

        $methods = [];
        $deferredMethods = [];
        $isSSLEnabled = $this->configurationAdapter->get('PS_SSL_ENABLED_EVERYWHERE');
        foreach ($apiMethods as $apiMethod) {
            $tipEnableSSL = false;
            if (Config::APPLEPAY === $apiMethod->id && !$isSSLEnabled) {
                $notAvailable[] = $apiMethod->id;
                $tipEnableSSL = true;
            }
            $deferredMethods[] = [
                'id' => $apiMethod->id,
                'name' => $apiMethod->description,
                'available' => !in_array($apiMethod->id, $notAvailable),
                'image' => (array) $apiMethod->image,
                'issuers' => $apiMethod->issuers,
                'tipEnableSSL' => $tipEnableSSL,
                'minimumAmount' => $apiMethod->minimumAmount ? [
                    'value' => NumberUtility::toPrecision(
                        $apiMethod->minimumAmount->value,
                        NumberUtility::FLOAT_PRECISION
                    ),
                    'currency' => $apiMethod->minimumAmount->currency,
                ] : false,
                'maximumAmount' => $apiMethod->maximumAmount ? [
                    'value' => NumberUtility::toPrecision(
                        $apiMethod->maximumAmount->value,
                        NumberUtility::FLOAT_PRECISION
                    ),
                    'currency' => $apiMethod->maximumAmount->currency,
                ] : false,
                'surcharge_fixed_amount_tax_incl' => 0,
            ];
        }

        ksort($methods);
        $methods = array_values($methods);
        foreach ($deferredMethods as $deferredMethod) {
            $methods[] = $deferredMethod;
        }

        $methods = $this->getMethodsObjForConfig($methods);
        $methods = $this->getMethodsCountriesForConfig($methods);
        $methods = $this->getExcludedCountriesForConfig($methods);
        $methods = $this->paymentMethodSortProvider->getSortedInAscendingWayForConfiguration($methods);

        return $methods;
    }

    private function getMethodsObjForConfig($apiMethods)
    {
        $this->environment = (int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT);

        $methods = [];
        $emptyPaymentMethod = new MolPaymentMethod();
        $emptyPaymentMethod->enabled = false;
        $emptyPaymentMethod->title = '';
        $emptyPaymentMethod->method = 'payments';
        $emptyPaymentMethod->description = '';
        $emptyPaymentMethod->is_countries_applicable = false;
        $emptyPaymentMethod->minimal_order_value = '';
        $emptyPaymentMethod->max_order_value = '';
        $emptyPaymentMethod->surcharge = 0;
        $emptyPaymentMethod->surcharge_fixed_amount_tax_excl = 0;
        $emptyPaymentMethod->tax_rules_group_id = 0;
        $emptyPaymentMethod->surcharge_percentage = '';
        $emptyPaymentMethod->surcharge_limit = '';

        foreach ($apiMethods as $apiMethod) {
            $paymentId = $this->methodRepository->getPaymentMethodIdByMethodId($apiMethod['id'], $this->environment);

            if ($paymentId) {
                $paymentMethod = new MolPaymentMethod((int) $paymentId);

                $paymentMethod = $this->toPrecisionForDecimalNumbers($paymentMethod);

                if (!empty($paymentMethod->surcharge_fixed_amount_tax_excl)) {
                    $apiMethod['surcharge_fixed_amount_tax_incl'] = $this->getSurchargeFixedAmountTaxInclPrice(
                        $paymentMethod->surcharge_fixed_amount_tax_excl,
                        $paymentMethod->tax_rules_group_id,
                        $this->context->getCountryId()
                    );

                    $paymentMethod->surcharge_fixed_amount_tax_excl = NumberUtility::toPrecision(
                        $paymentMethod->surcharge_fixed_amount_tax_excl,
                        NumberUtility::FLOAT_PRECISION
                    );

                    $apiMethod['surcharge_fixed_amount_tax_incl'] = NumberUtility::toPrecision(
                        $apiMethod['surcharge_fixed_amount_tax_incl'],
                        NumberUtility::FLOAT_PRECISION
                    );
                }

                $methods[$apiMethod['id']] = $apiMethod;
                $methods[$apiMethod['id']]['obj'] = $paymentMethod;

                continue;
            }
            $defaultPaymentMethod = clone $emptyPaymentMethod;

            $defaultPaymentMethod->id_method = $apiMethod['id'];
            $defaultPaymentMethod->method_name = $apiMethod['name'];

            $methods[$apiMethod['id']] = $apiMethod;
            $methods[$apiMethod['id']]['obj'] = $defaultPaymentMethod;
        }

        return $methods;
    }

    private function getMethodsCountriesForConfig(&$methods)
    {
        foreach ($methods as $key => $method) {
            $methods[$key]['countries'] = $this->countryRepository->getMethodCountryIds($method['obj']->id);
        }

        return $methods;
    }

    private function getExcludedCountriesForConfig(&$methods)
    {
        foreach ($methods as $key => $method) {
            $methods[$key]['excludedCountries'] = $this->countryRepository->getExcludedCountryIds($method['obj']->id);
        }

        return $methods;
    }

    /**
     * @param MollieApiClient $api
     * @param string $transactionId
     * @param bool $process Process the new payment/order status
     *
     * @return array|null
     *
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     * @since 3.3.2 $process option
     */
    public function getFilteredApiPayment($api, $transactionId, $process = false)
    {
        $payment = $api->payments->get($transactionId);
        if ($process) {
            $this->transactionService->processTransaction($payment);
        }

        if (method_exists($payment, 'refunds')) {
            $refunds = $payment->refunds();
            if (empty($refunds)) {
                $refunds = [];
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array) $refund,
                    array_flip([
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    ]));
            }, (array) $refunds);
            $payment = array_intersect_key(
                (array) $payment,
                array_flip([
                    'resource',
                    'id',
                    'mode',
                    'amount',
                    'settlementAmount',
                    'amountRefunded',
                    'amountRemaining',
                    'description',
                    'method',
                    'status',
                    'createdAt',
                    'paidAt',
                    'canceledAt',
                    'expiresAt',
                    'failedAt',
                    'metadata',
                    'isCancelable',
                ])
            );
            $payment['refunds'] = (array) $refunds;
        } else {
            $payment = null;
        }

        return $payment;
    }

    /**
     * @param MollieApiClient $api
     * @param string $transactionId
     *
     * @return array|MollieOrderAlias|null
     *
     * @throws ApiException
     */
    public function getFilteredApiOrder($api, $transactionId)
    {
        /** @var MollieOrderAlias $order */
        $mollieOrder = $api->orders->get(
            $transactionId,
            [
                'embed' => 'payments',
                'include' => [
                        'details' => 'remainderDetails',
                    ],
            ]
        );

        if (method_exists($mollieOrder, 'refunds')) {
            $refunds = $mollieOrder->refunds();
            if (empty($refunds)) {
                $refunds = [];
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array) $refund,
                    array_flip([
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    ]));
            }, (array) $refunds);
            $order = array_intersect_key(
                (array) $mollieOrder,
                array_flip([
                    'resource',
                    'id',
                    'mode',
                    'amount',
                    'settlementAmount',
                    'amountCaptured',
                    'status',
                    'method',
                    'metadata',
                    'isCancelable',
                    'createdAt',
                    'lines',
                ])
            );
            $order['refunds'] = (array) $refunds;
        } else {
            $order = null;
        }

        /** @var PaymentCollection $molliePayments */
        $molliePayments = $mollieOrder->payments();

        /** @var Payment $payment */
        foreach ($molliePayments as $payment) {
            $amountRemaining = [
                'value' => '0.00',
                'currency' => $payment->amount->currency,
            ];
            $order['availableRefundAmount'] = $payment->amountRemaining ?: $amountRemaining;
            $order['details'] = $payment->details !== null ? $payment->details : new \stdClass();
        }

        return $order;
    }

    /**
     * @param MollieApiClient|null $api
     * @param string $validationUrl
     *
     * @return string
     *
     * @throws ApiException
     * @throws MollieApiException
     */
    public function requestApplePayPaymentSession($api, string $validationUrl): string
    {
        if (!$api) {
            throw new MollieApiException('Mollie API is null. Check if API key is correct', MollieApiException::MOLLIE_API_IS_NULL);
        }

        return $api->wallets->requestApplePayPaymentSession($this->shop->domain, $validationUrl);
    }

    private function getSurchargeFixedAmountTaxInclPrice(float $priceTaxExcl, int $taxRulesGroupId, int $countryId): float
    {
        $taxCalculator = $this->taxCalculatorProvider->getTaxCalculator(
            $taxRulesGroupId,
            $countryId,
            0 // NOTE: there is no default state for back office so setting no state
        );

        return NumberUtility::toPrecision(
            $taxCalculator->addTaxes($priceTaxExcl),
            NumberUtility::FLOAT_PRECISION
        );
    }

    private function toPrecisionForDecimalNumbers(MolPaymentMethod $paymentMethod): MolPaymentMethod
    {
        $paymentMethod->surcharge_percentage = (string) NumberUtility::toPrecision(
            (float) $paymentMethod->surcharge_percentage,
            NumberUtility::FLOAT_PRECISION
        );

        $paymentMethod->surcharge_limit = (string) NumberUtility::toPrecision(
            (float) $paymentMethod->surcharge_limit,
            NumberUtility::FLOAT_PRECISION
        );

        $paymentMethod->min_amount = NumberUtility::toPrecision(
            $paymentMethod->min_amount,
            NumberUtility::FLOAT_PRECISION
        );

        $paymentMethod->max_amount = NumberUtility::toPrecision(
            $paymentMethod->max_amount,
            NumberUtility::FLOAT_PRECISION
        );

        return $paymentMethod;
    }
}
