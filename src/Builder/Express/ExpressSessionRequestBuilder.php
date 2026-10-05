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

namespace Mollie\Builder\Express;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\DTO\Express\ExpressSessionRequest;
use Mollie\DTO\PaymentFeeData;
use Mollie\Enum\LineType;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\CartLinesService;
use Mollie\Utility\HashUtility;
use Mollie\Utility\OrderNumberUtility;
use Mollie\Utility\SecureKeyUtility;
use Mollie\Utility\TextFormatUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Builds the Checkout Session body for a cart on the checkout payment step. The metadata and
 * webhook URL mirror PaymentMethodService::getPaymentData() so the payment Mollie creates for
 * the session passes the webhook and TransactionService checks unchanged.
 */
class ExpressSessionRequestBuilder
{
    const RETURN_CONTROLLER = 'expressReturn';

    /** Fields the Payments API line accepts but the Sessions API line does not. */
    const UNSUPPORTED_LINE_FIELDS = ['categories'];

    /** @var CartLinesService */
    private $cartLinesService;
    /** @var Context */
    private $context;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var ModuleFactory */
    private $moduleFactory;

    public function __construct(
        CartLinesService $cartLinesService,
        Context $context,
        ConfigurationAdapter $configuration,
        ModuleFactory $moduleFactory
    ) {
        $this->cartLinesService = $cartLinesService;
        $this->context = $context;
        $this->configuration = $configuration;
        $this->moduleFactory = $moduleFactory;
    }

    public function build(\Cart $cart): ExpressSessionRequest
    {
        $moduleName = (string) $this->moduleFactory->getModuleName();
        $cartId = (int) $cart->id;
        $customerId = (int) $cart->id_customer;
        // The payment step always renders in the cart's currency, same source hookPaymentOptions uses.
        $currencyIso = \Tools::strtoupper($this->context->getCurrencyIso());

        $amount = (float) TextFormatUtility::formatNumber(
            $cart->getOrderTotal(true, \Cart::BOTH),
            Config::API_ROUNDING_PRECISION
        );

        $orderReference = OrderNumberUtility::generateOrderNumber($cartId);
        $returnKey = SecureKeyUtility::generateReturnKey($customerId, $cartId, $moduleName);

        $redirectUrl = $this->context->getModuleLink(
            $moduleName,
            self::RETURN_CONTROLLER,
            [
                'cart_id' => $cartId,
                'key' => $returnKey,
                'utm_nooverride' => 1,
                'rand' => time(),
            ],
            true
        );

        $webhookUrl = $this->context->getModuleLink(
            $moduleName,
            'webhook',
            [
                'security_token' => HashUtility::hash($cart->secure_key),
            ],
            true
        );

        $lines = $this->cartLinesService->getCartLines(
            $amount,
            new PaymentFeeData(0.00, 0.00, 0.00, false),
            $currencyIso,
            $cart->getSummaryDetails(),
            $cart->getTotalShippingCost(null, true),
            $cart->getProducts(),
            (bool) $this->configuration->get('PS_GIFT_WRAPPING'),
            (string) $this->configuration->get(Config::MOLLIE_VOUCHER_CATEGORY),
            LineType::PAYMENT
        );

        return new ExpressSessionRequest(
            $currencyIso,
            TextFormatUtility::formatNumber($amount, Config::API_ROUNDING_PRECISION),
            $orderReference,
            $this->sanitizeLines($lines),
            $redirectUrl,
            $webhookUrl,
            [
                'cart_id' => $cartId,
                'order_reference' => $orderReference,
                'secure_key' => $returnKey,
                Config::EXPRESS_METADATA_FLAG => 1,
            ]
        );
    }

    /**
     * @param array<int, \JsonSerializable|array> $lines
     */
    public function sanitizeLines(array $lines): array
    {
        $result = [];

        foreach ($lines as $line) {
            $data = $line instanceof \JsonSerializable ? $line->jsonSerialize() : (array) $line;

            foreach (self::UNSUPPORTED_LINE_FIELDS as $field) {
                unset($data[$field]);
            }

            $result[] = array_filter($data, static function ($value) {
                return null !== $value && '' !== $value;
            });
        }

        return $result;
    }
}
