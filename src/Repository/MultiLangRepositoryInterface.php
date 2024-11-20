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
    public function getTextByLanguageAndMethod(int $idLanguage, string $idMethod, int $idShop): ?string;

    public function getExistingRecordId(string $idPaymentMethod, int $langId, int $idShop): ?string;

    public function getAllTranslationsByMethod(string $idPaymentMethod, int $langId, int $idShop): ?array;
}
