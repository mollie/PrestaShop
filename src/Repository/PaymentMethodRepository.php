<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Repository;

use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use Db;
use DbQuery;
use Exception;
use PrestaShopDatabaseException;
use PrestaShopException;

class PaymentMethodRepository
{
    /**
     * @param $paymentMethodId
     * @return false|string|null
     */
    public function getPaymentMethodIssuersByPaymentMethodId($paymentMethodId)
    {
        $sql = 'Select issuers_json FROM `' . _DB_PREFIX_ . 'mol_payment_method_issuer` WHERE id_payment_method = "' . pSQL($paymentMethodId) . '"';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * @param $paymentMethodId
     * @return bool
     */
    public function deletePaymentMethodIssuersByPaymentMethodId($paymentMethodId)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'mol_payment_method_issuer` WHERE id_payment_method = "' . pSQL($paymentMethodId) . '"';

        return Db::getInstance()->execute($sql);
    }

    /**
     * @param $paymentMethodId
     * @param $environment
     * @return false|string|null
     */
    public function getPaymentMethodIdByMethodId($paymentMethodId, $environment)
    {
        $sql = 'SELECT id_payment_method FROM `' . _DB_PREFIX_ . 'mol_payment_method` 
        WHERE id_method = "' . pSQL($paymentMethodId) . '" AND live_environment = "' . (int)$environment . '"';

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

    /**
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function getMethodsForCheckout($environment)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('mol_payment_method');
        $sql->where('live_environment = ' . pSQL($environment));

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $oldTransactionId
     * @param $newTransactionId
     * @return bool
     */
    public function updateTransactionId($oldTransactionId, $newTransactionId)
    {
        return Db::getInstance()->update(
            'mollie_payments',
            [
                'transaction_id'  => pSQL($newTransactionId),

            ],
            '`transaction_id` = \'' . pSQL($oldTransactionId) . '\''
        );
    }

    public function savePaymentStatus($transactionId, $status, $orderId, $paymentMethod)
    {
        try {
            return Db::getInstance()->update(
                'mollie_payments',
                [
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'bank_status' => pSQL($status),
                    'order_id' => (int)$orderId,
                    'method' => pSQL($paymentMethod),
                ],
                '`transaction_id` = \'' . pSQL($transactionId) . '\''
            );
        } catch (Exception $e) {
            $this->tryAddOrderReferenceColumn();
            throw $e;
        }
    }

    public function addOpenStatusPayment($cartId, $orderPayment, $transactionId, $orderId, $orderReference)
    {
        return  Db::getInstance()->insert(
            'mollie_payments',
            [
                'cart_id' => (int)$cartId,
                'method' => pSQL($orderPayment),
                'transaction_id' => pSQL($transactionId),
                'bank_status' => PaymentStatus::STATUS_OPEN,
                'order_id' => (int) $orderId,
                'order_reference' => psql($orderReference),
                'created_at' => array('type' => 'sql', 'value' => 'NOW()'),
            ]
        );
    }
}
