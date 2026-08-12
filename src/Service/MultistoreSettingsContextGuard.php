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

declare(strict_types=1);

namespace Mollie\Service;

use Mollie\Adapter\Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Mollie stores its settings (payment methods, API keys, advanced options) per shop.
 * When the back office is set to "All stores" or a shop group, PrestaShop points the
 * context at the default shop, so saving there silently writes to the main shop only
 * and the change never reaches the other shops. To avoid that, settings can only be
 * edited when a single shop is selected.
 */
class MultistoreSettingsContextGuard
{
    /** @var Shop */
    private $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function canEditSettings(): bool
    {
        return \Shop::CONTEXT_SHOP === $this->shop->getContext();
    }
}
