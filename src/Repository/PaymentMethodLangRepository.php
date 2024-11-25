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

use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodLangRepository extends AbstractRepository implements PaymentMethodLangRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MolPaymentMethodLang::class);
    }

    public function getTextByLanguageAndMethod(int $idLanguage, string $idMethod, int $idShop): ?string
    {
        $sql = new \DbQuery();
        $sql->select('`text`');
        $sql->from('mol_payment_method_lang');
        $sql->where('`id_method` = "' . pSQL($idMethod) . '"');
        $sql->where('`id_lang` = ' . $idLanguage);
        $sql->where('`id_shop` = ' . $idShop);

        return \Db::getInstance()->getValue($sql) ?: null;
    }

    public function getAllTranslationsByMethod(string $idPaymentMethod, int $idShop): array
    {
        $sql = new \DbQuery();
        $sql->select('`id_lang`, `text`');
        $sql->from('mol_payment_method_lang');
        $sql->where('`id_method` = "' . pSQL($idPaymentMethod) . '"');
        $sql->where('`id_shop` = ' . $idShop);

        return \Db::getInstance()->executeS($sql) ?? [];
    }
}
