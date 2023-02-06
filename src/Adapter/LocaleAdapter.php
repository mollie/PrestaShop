<?php

declare(strict_types=1);

namespace Mollie\Adapter;

use PrestaShop\PrestaShop\Core\Localization\Locale;

class LocaleAdapter
{
    public function __construct(Context $context)
    {
        $this->locale = Tools::getContextLocale($context);
    }

    public function formatPrice(float $number, string $currencyCode)
    {
        return Locale::formatPrice($number, $currencyCode);
    }
}
