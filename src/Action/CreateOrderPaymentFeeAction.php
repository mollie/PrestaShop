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

namespace Mollie\Action;

use Exception;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\Exception\CouldNotCreateOrderPaymentFee;
use MolOrderPaymentFee;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateOrderPaymentFeeAction
{
    /**
     * @throws CouldNotCreateOrderPaymentFee
     */
    public function run(CreateOrderPaymentFeeActionData $data): void
    {
        try {
            $molOrderPaymentFee = new MolOrderPaymentFee();

            $molOrderPaymentFee->id_cart = $data->getCartId();
            $molOrderPaymentFee->id_order = $data->getOrderId();
            $molOrderPaymentFee->fee_tax_incl = $data->getPaymentFeeTaxIncl();
            $molOrderPaymentFee->fee_tax_excl = $data->getPaymentFeeTaxExcl();

            $molOrderPaymentFee->save();
        } catch (Exception $exception) {
            throw CouldNotCreateOrderPaymentFee::failedToInsertOrderPaymentFee($exception);
        }
    }
}
