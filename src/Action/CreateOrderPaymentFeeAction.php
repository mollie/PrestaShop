<?php

namespace Mollie\Action;

use Exception;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\Exception\CouldNotCreateOrderPaymentFee;
use MolOrderPaymentFee;

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
