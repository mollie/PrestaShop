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

namespace Mollie\Adapter;

class Language
{
    public function getDefaultLanguageId(): int
    {
        return (int) \Configuration::get('PS_LANG_DEFAULT');
    }

    public function getAllLanguages(): array
    {
        return \Language::getLanguages(false);
    }

    public function getContextLanguage(): \Language
    {
        return \Context::getContext()->language;
    }
}
