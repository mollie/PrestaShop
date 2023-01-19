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

namespace Mollie\Presenter;

use Context;
use Mollie;
use Mollie\Adapter\Smarty;
use Smarty_Data;

class OrderListActionBuilder
{
    const FILE_NAME = 'OrderListActionBuilder';
    /**
     * @var Mollie
     */
    private $mollie;
    /** @var Smarty */
    private $smarty;

    public function __construct(Mollie $mollie, Smarty $smarty)
    {
        $this->mollie = $mollie;
        $this->smarty = $smarty;
    }

    public function buildOrderPaymentResendButton($orderId)
    {
        $this->smarty->assign([
            'idOrder' => (int) $orderId,
            'message' => $this->mollie->l(
                'You will resend email with payment link to the customer',
                self::FILE_NAME
            ),
            'orderListIcon' => $this->mollie->getPathUri() . 'views/img/second_chance.png',
        ]);

        return $this->mollie->display(
            $this->mollie->getLocalPath(),
            'views/templates/hook/admin/order-list-icon.tpl'
        );
    }
}
