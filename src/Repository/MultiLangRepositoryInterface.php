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

namespace Mollie\Repository;

use Mollie\Shared\Infrastructure\Repository\ReadOnlyRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface MultiLangRepositoryInterface extends ReadOnlyRepositoryInterface
{

    /**
     * Gets translated text by language and method id
     */
    public function getTextByLanguageAndMethod(int $idLanguage, string $idMethod, int $idShop): ?string;

    /**
     * Gets auto increment ID for specific payment method
     */
    public function getExistingRecordId(string $idPaymentMethod, int $langId, int $idShop): ?string;

    /**
     * Gets all translations for a payment method title
     *
     * @param string $idMethod
     * @return array [id_lang => text] for instance: [74 => 'Apelo Pay', 68 => 'Apella Pia']
     */
    public function getAllTranslationsByMethod(string $idPaymentMethod, int $idShop): ?array;
}
