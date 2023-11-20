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

namespace Mollie\Tests\Integration\src\Action;

use Mollie\Action\CreateOrderPaymentFeeAction;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\Tests\Integration\BaseTestCase;

class CreateOrderPaymentFeeActionTest extends BaseTestCase
{
    public function testItSuccessfullyUpdatesOrderTotal(): void
    {
        /** @var CreateOrderPaymentFeeAction $createOrderPaymentFeeAction */
        $createOrderPaymentFeeAction = $this->getService(CreateOrderPaymentFeeAction::class);

        $this->assertDatabaseHasNot(\MolOrderPaymentFee::class,
            [
                'id_cart' => 1,
                'id_order' => 1,
                'fee_tax_incl' => 12.1,
                'fee_tax_excl' => 10,
            ]
        );

        $createOrderPaymentFeeAction->run(CreateOrderPaymentFeeActionData::create(
            1,
            1,
            12.1,
            10
        ));

        $this->assertDatabaseHas(\MolOrderPaymentFee::class,
            [
                'id_cart' => 1,
                'id_order' => 1,
                'fee_tax_incl' => 12.1,
                'fee_tax_excl' => 10,
            ]
        );
    }
}
