<?php

namespace Mollie\Repository;

use Db;

class PaymentMethodRepository
{
    public function getPaymentMethodIssuersByPaymentMethodId($paymentMethodId)
    {
        $sql = 'Select issuers_json FROM `' . _DB_PREFIX_ . 'mol_payment_method_issuer` WHERE id_payment_method = "' . pSQL($paymentMethodId) . '"';

        return Db::getInstance()->getValue($sql);
    }

    public function deletePaymentMethodIssuersByPaymentMethodId($paymentMethodId)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'mol_payment_method_issuer` WHERE id_payment_method = "' . pSQL($paymentMethodId) . '"';

        return Db::getInstance()->execute($sql);
    }

    public function getPaymentMethodIdByMethodId($paymentMethodId)
    {
        $sql = 'SELECT id_payment_method FROM `' . _DB_PREFIX_ . 'mol_payment_method` WHERE id_method = "' . pSQL($paymentMethodId) . '"';

        return Db::getInstance()->getValue($sql);
    }
}