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
use Mollie\Exception\MollieException;
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
    const SHIPPING_CONTROLLER = 'expressShipping';
    const SHIPPING_FEE_LINE_TYPE = 'shipping_fee';

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

    /**
     * @param bool $collectDetails Mollie collects the customer and addresses and asks the shop for
     *                             carriers through the shipping callback; the amount excludes shipping
     *
     * @throws MollieException when the collect mode lines do not add up to the amount
     */
    public function build(\Cart $cart, bool $collectDetails = false): ExpressSessionRequest
    {
        $moduleName = (string) $this->moduleFactory->getModuleName();
        $cartId = (int) $cart->id;
        $customerId = (int) $cart->id_customer;
        // The payment step always renders in the cart's currency, same source hookPaymentOptions uses.
        $currencyIso = \Tools::strtoupper($this->context->getCurrencyIso());

        $amount = $this->getSessionAmount($cart, $collectDetails);

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

        $cartSummary = $cart->getSummaryDetails();

        if ($collectDetails) {
            // Mollie adds the shipping option the shopper picks, so the lines carry no shipping.
            $cartSummary['total_shipping'] = 0;
            $cartSummary['total_shipping_tax_exc'] = 0;
        }

        $lines = $this->sanitizeLines($this->cartLinesService->getCartLines(
            $amount,
            new PaymentFeeData(0.00, 0.00, 0.00, false),
            $currencyIso,
            $cartSummary,
            $cart->getProducts(),
            (bool) $this->configuration->get('PS_GIFT_WRAPPING'),
            (string) $this->configuration->get(Config::MOLLIE_VOUCHER_CATEGORY),
            LineType::PAYMENT
        ));

        $metadata = [
            'cart_id' => $cartId,
            'order_reference' => $orderReference,
            'secure_key' => $returnKey,
            Config::EXPRESS_METADATA_FLAG => 1,
        ];

        if (!$collectDetails) {
            return new ExpressSessionRequest(
                $currencyIso,
                TextFormatUtility::formatNumber($amount, Config::API_ROUNDING_PRECISION),
                $orderReference,
                $lines,
                $redirectUrl,
                $webhookUrl,
                $metadata
            );
        }

        $this->assertCollectLinesMatchAmount($lines, $amount, $cartId);

        $metadata[Config::EXPRESS_COLLECT_METADATA_FLAG] = 1;
        $requiredCustomerDetails = ['email', 'billing-address'];
        $shipping = [];

        if (!$cart->isVirtualCart()) {
            $requiredCustomerDetails[] = 'shipping-address';
            $shipping = [
                'callbackUrl' => $this->context->getModuleLink(
                    $moduleName,
                    self::SHIPPING_CONTROLLER,
                    [
                        'cart_id' => $cartId,
                        'token' => $this->getShippingCallbackToken($cart),
                    ],
                    true
                ),
            ];
        }

        return new ExpressSessionRequest(
            $currencyIso,
            TextFormatUtility::formatNumber($amount, Config::API_ROUNDING_PRECISION),
            $orderReference,
            $lines,
            $redirectUrl,
            $webhookUrl,
            $metadata,
            $requiredCustomerDetails,
            $shipping
        );
    }

    /**
     * Payment mode charges the whole cart. Collect mode leaves shipping out: Mollie adds the
     * option the shopper picks through the shipping callback.
     */
    public function getSessionAmount(\Cart $cart, bool $collectDetails): float
    {
        $type = $collectDetails ? \Cart::BOTH_WITHOUT_SHIPPING : \Cart::BOTH;

        return (float) TextFormatUtility::formatNumber(
            $cart->getOrderTotal(true, $type),
            Config::API_ROUNDING_PRECISION
        );
    }

    public function getShippingCallbackToken(\Cart $cart): string
    {
        return HashUtility::hash($cart->secure_key . 'shipping');
    }

    /**
     * A shipping_fee line next to a shipping callback, or lines that do not add up to the amount,
     * would make Mollie refuse the session or charge a total the cart does not have.
     *
     * @throws MollieException
     */
    private function assertCollectLinesMatchAmount(array $lines, float $amount, int $cartId): void
    {
        $total = 0.0;

        foreach ($lines as $line) {
            if (self::SHIPPING_FEE_LINE_TYPE === ($line['type'] ?? null)) {
                throw new MollieException(sprintf('Express collect session for cart %d has a shipping line', $cartId));
            }

            $total += (float) ($line['totalAmount']['value'] ?? 0);
        }

        if (round($total, 2) !== round($amount, 2)) {
            throw new MollieException(sprintf('Express collect session lines for cart %d add up to %.2f instead of %.2f', $cartId, $total, $amount));
        }
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
