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

namespace Mollie\Grid\Query\Modifier;

use Doctrine\DBAL\Query\QueryBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface GridQueryModifierInterface
{
    /**
     * Used to modify Grid Query Builder.
     */
    public function modify(QueryBuilder $queryBuilder);
}
