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

namespace Mollie\Handler\Settings;

use Db;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerGroupRestrictionHandler implements CustomerGroupRestrictionHandlerInterface
{
    /** @var ToolsAdapter */
    private $tools;

    public function __construct(ToolsAdapter $tools)
    {
        $this->tools = $tools;
    }

    /**
     * Save customer group restrictions for a payment method
     *
     * @param int $paymentMethodId
     * @param string $methodId
     *
     * @return void
     */
    public function saveRestrictions($paymentMethodId, $methodId)
    {
        Db::getInstance()->delete(
            'mol_excluded_customer_groups',
            'id_payment_method = ' . (int) $paymentMethodId
        );

        $selectedGroups = $this->tools->getValue(Config::MOLLIE_METHOD_CUSTOMER_GROUPS . $methodId);

        if (!empty($selectedGroups)) {
            foreach ($selectedGroups as $groupId) {
                Db::getInstance()->insert(
                    'mol_excluded_customer_groups',
                    [
                        'id_payment_method' => (int) $paymentMethodId,
                        'id_customer_group' => (int) $groupId,
                    ]
                );
            }
        }
    }
}
