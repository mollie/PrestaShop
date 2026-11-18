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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Adapter\ToolsAdapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The shop's minimum purchase total (Order settings). PrestaShop enforces it in the checkout
 * controllers only, so express flows that skip checkout must check it themselves.
 */
class MinimumPurchaseService
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var ToolsAdapter */
    private $tools;
    /** @var Context */
    private $context;

    public function __construct(ConfigurationAdapter $configuration, ToolsAdapter $tools, Context $context)
    {
        $this->configuration = $configuration;
        $this->tools = $tools;
        $this->context = $context;
    }

    public function isBelowMinimum(\Cart $cart): bool
    {
        return $this->getShortfall($cart) !== null;
    }

    /** Same text the cart page shows, from the core Shop.Theme.Checkout domain, empty when the minimum is reached. */
    public function getNotReachedMessage(\Cart $cart): string
    {
        $shortfall = $this->getShortfall($cart);

        if ($shortfall === null) {
            return '';
        }

        return $this->context->trans(
            'A minimum shopping cart total of %amount% (tax excl.) is required to validate your order. Current cart total is %total% (tax excl.).',
            [
                '%amount%' => $this->tools->displayPrice($shortfall['minimum'], $shortfall['currency']),
                '%total%' => $this->tools->displayPrice($shortfall['total'], $shortfall['currency']),
            ],
            'Shop.Theme.Checkout'
        );
    }

    /**
     * The minimum is converted to the cart currency, as the core cart presenter does.
     *
     * @return array{minimum: float, total: float, currency: \Currency}|null null when the minimum is reached
     */
    private function getShortfall(\Cart $cart)
    {
        $minimum = (float) $this->configuration->get('PS_PURCHASE_MINIMUM');

        if ($minimum <= 0) {
            return null;
        }

        $currency = new \Currency((int) $cart->id_currency);
        $minimalPurchase = \Tools::convertPrice($minimum, $currency);
        \Hook::exec('overrideMinimalPurchasePrice', ['minimalPurchase' => &$minimalPurchase]);
        $total = (float) $cart->getOrderTotal(false, \Cart::ONLY_PRODUCTS);

        if ($total >= (float) $minimalPurchase) {
            return null;
        }

        return ['minimum' => (float) $minimalPurchase, 'total' => $total, 'currency' => $currency];
    }
}
