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
    /**
     * @return int
     **/
    public function getDefaultLanguageId()
    {
        return \Configuration::get('PS_LANG_DEFAULT');
    }

    /**
     * @return array
     **/
    public function getAllLanguages()
    {
        return \Language::getLanguages(false);
    }

    public function getContextLanguage()
    {
        return \Context::getContext()->language;

    }
}
