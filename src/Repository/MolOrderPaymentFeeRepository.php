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

use MolOrderPaymentFee;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MolOrderPaymentFeeRepository extends AbstractRepository implements MolOrderPaymentFeeRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(MolOrderPaymentFee::class);
    }
}
