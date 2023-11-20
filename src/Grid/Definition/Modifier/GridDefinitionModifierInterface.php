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

namespace Mollie\Grid\Definition\Modifier;

use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface GridDefinitionModifierInterface
{
    /**
     * Used to modify Grid Definition.
     *
     * @param GridDefinitionInterface $gridDefinition
     */
    public function modify(GridDefinitionInterface $gridDefinition);
}
