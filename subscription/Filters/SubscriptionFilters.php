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

namespace Mollie\Subscription\Filters;

use Mollie\Subscription\Grid\SubscriptionGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionFilters extends Filters
{
    /** @var string */
    protected $filterId = SubscriptionGridDefinitionFactory::GRID_ID;

    /**
     * {@inheritdoc}
     */
    public static function getDefaults()
    {
        return [
            'limit' => 50,
            'offset' => 0,
            'orderBy' => 'id_mol_recurring_order',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
