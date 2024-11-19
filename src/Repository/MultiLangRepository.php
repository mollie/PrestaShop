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
    public function getTextByLanguageId(int $idLanguage): string
    {
        $sql = new \DbQuery();
        $sql->select('`text`');
        $sql->from('mol_payment_method_lang');
        $sql->where('`id_lang` = "' . (int) $idLanguage . '"');
        $sql->limit(1);

        return \Db::getInstance()->getValue($sql) ?: '';
    }
}
