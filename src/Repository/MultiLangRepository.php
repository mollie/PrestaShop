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

class MultiLangRepository
{
    public function getTextByLanguageAndMethod(int $idLanguage, string $idMethod): string
    {
        $sql = new \DbQuery();
        $sql->select('`text`');
        $sql->from('mol_payment_method_lang');
        $sql->where('`id_method` = "' . pSQL($idMethod) . '"');
        $sql->where('`id_lang` = ' . (int) $idLanguage);

        return \Db::getInstance()->getValue($sql) ?: '';
    }

    public function getExistingRecordId(string $idPaymentMethod, int $langId): ?string
    {
        $sql = new \DbQuery();
        $sql->select('`id`');
        $sql->from('mol_payment_method_lang');
        $sql->where('`id_method` = "' . pSQL($idPaymentMethod) . '"');
        $sql->where('`id_lang` = ' . (int) $langId);

        return \Db::getInstance()->getValue($sql) ?: null;
    }
}
