<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api;

use _PhpScoper5ea00cc67502b\Composer\CaBundle\CaBundle;
use _PhpScoper5ea00cc67502b\GuzzleHttp\Client;
use _PhpScoper5ea00cc67502b\GuzzleHttp\ClientInterface;
use _PhpScoper5ea00cc67502b\GuzzleHttp\Exception\GuzzleException;
use _PhpScoper5ea00cc67502b\GuzzleHttp\Psr7\Request;
use _PhpScoper5ea00cc67502b\GuzzleHttp\RequestOptions;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\ChargebackEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\CustomerEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\CustomerPaymentsEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\InvoiceEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\MandateEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\MethodEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OnboardingEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OrderEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OrderLineEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OrderPaymentEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OrderRefundEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\PaymentCaptureEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\OrganizationEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\PaymentChargebackEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\PaymentEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\PaymentRefundEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\PermissionEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\ProfileEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\ProfileMethodEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\RefundEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\SettlementsEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\ShipmentEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\SubscriptionEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\WalletEndpoint;
use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\IncompatiblePlatform;
use _PhpScoper5ea00cc67502b\Psr\Http\Message\ResponseInterface;
use _PhpScoper5ea00cc67502b\Psr\Http\Message\StreamInterface;
use stdClass;
use function function_exists;
use function implode;
use function json_decode;
use function json_last_error;
use function php_uname;
use function phpversion;
use function preg_match;
use function rtrim;
use function str_replace;
use function trim;
use const JSON_ERROR_NONE;

class MollieApiClient
{
    /**
     * Version of our client.
     */
    const CLIENT_VERSION = "2.16.0";
    /**
     * Endpoint of the remote API.
     */
    const API_ENDPOINT = "https://api.mollie.com";
    /**
     * Version of the remote API.
     */
    const API_VERSION = "v2";
    /**
     * HTTP Methods
     */
    const HTTP_GET = "GET";
    const HTTP_POST = "POST";
    const HTTP_DELETE = "DELETE";
    const HTTP_PATCH = "PATCH";
    /**
     * HTTP status codes
     */
    const HTTP_NO_CONTENT = 204;
    /**
     * Default response timeout (in seconds).
     */
    const TIMEOUT = 10;
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $apiEndpoint = self::API_ENDPOINT;
    /**
     * RESTful Payments resource.
     *
     * @var PaymentEndpoint
     */
    public $payments;
    /**
     * RESTful Methods resource.
     *
     * @var MethodEndpoint
     */
    public $methods;
    /**
     * @var ProfileMethodEndpoint
     */
    public $profileMethods;
    /**
     * RESTful Customers resource.
     *
     * @var CustomerEndpoint
     */
    public $customers;
    /**
     * RESTful Customer payments resource.
     *
     * @var CustomerPaymentsEndpoint
     */
    public $customerPayments;
    /**
     * @var SettlementsEndpoint
     */
    public $settlements;
    /**
     * RESTful Subscription resource.
     *
     * @var SubscriptionEndpoint
     */
    public $subscriptions;
    /**
     * RESTful Mandate resource.
     *
     * @var MandateEndpoint
     */
    public $mandates;
    /**
     * @var ProfileEndpoint
     */
    public $profiles;
    /**
     * RESTful Organization resource.
     *
     * @var OrganizationEndpoint
     */
    public $organizations;
    /**
     * RESTful Permission resource.
     *
     * @var PermissionEndpoint
     */
    public $permissions;
    /**
     * RESTful Invoice resource.
     *
     * @var InvoiceEndpoint
     */
    public $invoices;
    /**
     * RESTful Onboarding resource.
     *
     * @var OnboardingEndpoint
     */
    public $onboarding;
    /**
     * RESTful Order resource.
     *
     * @var OrderEndpoint
     */
    public $orders;
    /**
     * RESTful OrderLine resource.
     *
     * @var OrderLineEndpoint
     */
    public $orderLines;
    /**
     * RESTful OrderPayment resource.
     *
     * @var OrderPaymentEndpoint
     */
    public $orderPayments;
    /**
     * RESTful Shipment resource.
     *
     * @var ShipmentEndpoint
     */
    public $shipments;
    /**
     * RESTful Refunds resource.
     *
     * @var RefundEndpoint
     */
    public $refunds;
    /**
     * RESTful Payment Refunds resource.
     *
     * @var PaymentRefundEndpoint
     */
    public $paymentRefunds;
    /**
     * RESTful Payment Captures resource.
     *
     * @var PaymentCaptureEndpoint
     */
    public $paymentCaptures;
    /**
     * RESTful Chargebacks resource.
     *
     * @var ChargebackEndpoint
     */
    public $chargebacks;
    /**
     * RESTful Payment Chargebacks resource.
     *
     * @var PaymentChargebackEndpoint
     */
    public $paymentChargebacks;
    /**
     * RESTful Order Refunds resource.
     *
     * @var OrderRefundEndpoint
     */
    public $orderRefunds;
    /**
     * Manages Wallet requests
     *
     * @var WalletEndpoint
     */
    public $wallets;
    /**
     * @var string
     */
    protected $apiKey;
    /**
     * True if an OAuth access token is set as API key.
     *
     * @var bool
     */
    protected $oauthAccess;
    /**
     * @var array
     */
    protected $versionStrings = [];
    /**
     * @var int
     */
    protected $lastHttpResponseStatusCode;
    /**
     * @param ClientInterface $httpClient
     *
     * @throws IncompatiblePlatform
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new Client([RequestOptions::VERIFY => CaBundle::getBundledCaBundlePath(), RequestOptions::TIMEOUT => self::TIMEOUT]);
        $compatibilityChecker = new CompatibilityChecker();
        $compatibilityChecker->checkCompatibility();
        $this->initializeEndpoints();
        $this->addVersionString("Mollie/" . self::CLIENT_VERSION);
        $this->addVersionString("PHP/" . phpversion());
        $this->addVersionString("Guzzle/" . ClientInterface::VERSION);
    }
    public function initializeEndpoints()
    {
        $this->payments = new PaymentEndpoint($this);
        $this->methods = new MethodEndpoint($this);
        $this->profileMethods = new ProfileMethodEndpoint($this);
        $this->customers = new CustomerEndpoint($this);
        $this->settlements = new SettlementsEndpoint($this);
        $this->subscriptions = new SubscriptionEndpoint($this);
        $this->customerPayments = new CustomerPaymentsEndpoint($this);
        $this->mandates = new MandateEndpoint($this);
        $this->invoices = new InvoiceEndpoint($this);
        $this->permissions = new PermissionEndpoint($this);
        $this->profiles = new ProfileEndpoint($this);
        $this->onboarding = new OnboardingEndpoint($this);
        $this->organizations = new OrganizationEndpoint($this);
        $this->orders = new OrderEndpoint($this);
        $this->orderLines = new OrderLineEndpoint($this);
        $this->orderPayments = new OrderPaymentEndpoint($this);
        $this->orderRefunds = new OrderRefundEndpoint($this);
        $this->shipments = new ShipmentEndpoint($this);
        $this->refunds = new RefundEndpoint($this);
        $this->paymentRefunds = new PaymentRefundEndpoint($this);
        $this->paymentCaptures = new PaymentCaptureEndpoint($this);
        $this->chargebacks = new ChargebackEndpoint($this);
        $this->paymentChargebacks = new PaymentChargebackEndpoint($this);
        $this->wallets = new WalletEndpoint($this);
    }
    /**
     * @param string $url
     *
     * @return MollieApiClient
     */
    public function setApiEndpoint($url)
    {
        $this->apiEndpoint = rtrim(trim($url), '/');
        return $this;
    }
    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }
    /**
     * @param string $apiKey The Mollie API key, starting with 'test_' or 'live_'
     *
     * @return MollieApiClient
     * @throws ApiException
     */
    public function setApiKey($apiKey)
    {
        $apiKey = trim($apiKey);
        if (!preg_match('/^(live|test)_\\w{30,}$/', $apiKey)) {
            throw new ApiException("Invalid API key: '{$apiKey}'. An API key must start with 'test_' or 'live_' and must be at least 30 characters long.");
        }
        $this->apiKey = $apiKey;
        $this->oauthAccess = false;
        return $this;
    }
    /**
     * @param string $accessToken OAuth access token, starting with 'access_'
     *
     * @return MollieApiClient
     * @throws ApiException
     */
    public function setAccessToken($accessToken)
    {
        $accessToken = trim($accessToken);
        if (!preg_match('/^access_\\w+$/', $accessToken)) {
            throw new ApiException("Invalid OAuth access token: '{$accessToken}'. An access token must start with 'access_'.");
        }
        $this->apiKey = $accessToken;
        $this->oauthAccess = true;
        return $this;
    }
    /**
     * Returns null if no API key has been set yet.
     *
     * @return bool|null
     */
    public function usesOAuth()
    {
        return $this->oauthAccess;
    }
    /**
     * @param string $versionString
     *
     * @return MollieApiClient
     */
    public function addVersionString($versionString)
    {
        $this->versionStrings[] = str_replace([" ", "\t", "\n", "\r"], '-', $versionString);
        return $this;
    }
    /**
     * Perform an http call. This method is used by the resource specific classes. Please use the $payments property to
     * perform operations on payments.
     *
     * @param string $httpMethod
     * @param string $apiMethod
     * @param string|null|resource|StreamInterface $httpBody
     *
     * @return stdClass
     * @throws ApiException
     *
     * @codeCoverageIgnore
     */
    public function performHttpCall($httpMethod, $apiMethod, $httpBody = null)
    {
        $url = $this->apiEndpoint . "/" . self::API_VERSION . "/" . $apiMethod;
        return $this->performHttpCallToFullUrl($httpMethod, $url, $httpBody);
    }
    /**
     * Perform an http call to a full url. This method is used by the resource specific classes.
     *
     * @see $payments
     * @see $isuers
     *
     * @param string $httpMethod
     * @param string $url
     * @param string|null|resource|StreamInterface $httpBody
     *
     * @return stdClass|null
     * @throws ApiException
     *
     * @codeCoverageIgnore
     */
    public function performHttpCallToFullUrl($httpMethod, $url, $httpBody = null)
    {
        if (empty($this->apiKey)) {
            throw new ApiException("You have not set an API key or OAuth access token. Please use setApiKey() to set the API key.");
        }
        $userAgent = implode(' ', $this->versionStrings);
        if ($this->usesOAuth()) {
            $userAgent .= " OAuth/2.0";
        }
        $headers = ['Accept' => "application/json", 'Authorization' => "Bearer {$this->apiKey}", 'User-Agent' => $userAgent];
        if (function_exists("php_uname")) {
            $headers['X-Mollie-Client-Info'] = php_uname();
        }
        $request = new Request($httpMethod, $url, $headers, $httpBody);
        try {
            $response = $this->httpClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw ApiException::createFromGuzzleException($e);
        }
        if (!$response) {
            throw new ApiException("Did not receive API response.");
        }
        return $this->parseResponseBody($response);
    }
    /**
     * Parse the PSR-7 Response body
     *
     * @param ResponseInterface $response
     * @return stdClass|null
     * @throws ApiException
     */
    private function parseResponseBody(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        if (empty($body)) {
            if ($response->getStatusCode() === self::HTTP_NO_CONTENT) {
                return null;
            }
            throw new ApiException("No response body found.");
        }
        $object = @json_decode($body);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Unable to decode Mollie response: '{$body}'.");
        }
        if ($response->getStatusCode() >= 400) {
            throw ApiException::createFromResponse($response);
        }
        return $object;
    }
    /**
     * Serialization can be used for caching. Of course doing so can be dangerous but some like to live dangerously.
     *
     * \serialize() should be called on the collections or object you want to cache.
     *
     * We don't need any property that can be set by the constructor, only properties that are set by setters.
     *
     * Note that the API key is not serialized, so you need to set the key again after unserializing if you want to do
     * more API calls.
     *
     * @deprecated
     * @return string[]
     */
    public function __sleep()
    {
        return ["apiEndpoint"];
    }
    /**
     * When unserializing a collection or a resource, this class should restore itself.
     *
     * Note that if you use a custom GuzzleClient, this client is lost. You can't re set the Client, so you should
     * probably not use this feature.
     *
     * @throws IncompatiblePlatform If suddenly unserialized on an incompatible platform.
     */
    public function __wakeup()
    {
        $this->__construct();
    }
}
