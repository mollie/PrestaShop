<?php

namespace Mollie\Repository;

use _PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentStatus;
use Db;
use DbQuery;
use PrestaShopDatabaseException;
use PrestaShopException;

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


    /**
     * @param string $column
     * @param int $id
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0 static function
     */
    public function getPaymentBy($column, $id)
    {
        try {
            $paidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\' AND `bank_status` IN(\'%s\', \'%s\')',
                    _DB_PREFIX_ . 'mollie_payments',
                    bqSQL($column),
                    pSQL($id),
                    PaymentStatus::STATUS_PAID,
                    PaymentStatus::STATUS_AUTHORIZED
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            static::tryAddOrderReferenceColumn();
            throw $e;
        }

        if ($paidPayment) {
            return $paidPayment;
        }

        try {
            $nonPaidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\' ORDER BY `created_at` DESC',
                    _DB_PREFIX_ . 'mollie_payments',
                    bqSQL($column),
                    pSQL($id)
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            $this->tryAddOrderReferenceColumn();
            throw $e;
        }

        return $nonPaidPayment;
    }

    /**
     * Add the order reference column in case the module upgrade script hasn't run
     *
     * @return bool
     *
     * @since 3.3.0
     */
    public function tryAddOrderReferenceColumn()
    {
        try {
            if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\'
                AND TABLE_NAME = \'' . _DB_PREFIX_ . 'mollie_payments\'
                AND COLUMN_NAME = \'order_reference\'')
            ) {
                return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments` ADD `order_reference` varchar(191)');
            }
        } catch (PrestaShopException $e) {
            return false;
        }

        return true;
    }

    public function getMethodIdsForCheckout()
    {
        $sql = new DbQuery();
        $sql->select('`id_payment_method`');
        $sql->from('mol_payment_method');

        return Db::getInstance()->executeS($sql);
    }
}