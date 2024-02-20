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

namespace Mollie\Subscription\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LanguageRepository
{
    public function getDefaultLanguageId(): int
    {
        return (int) \Configuration::get('PS_LANG_DEFAULT');
    }

    public function getAllLanguages(): array
    {
        return \Language::getLanguages(false);
    }
}
