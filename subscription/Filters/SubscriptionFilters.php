<?php

namespace Mollie\Subscription\Filters;

use Mollie\Subscription\Grid\SubscriptionGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

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
            'orderBy' => 'id_mol_sub_recurring_order',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
