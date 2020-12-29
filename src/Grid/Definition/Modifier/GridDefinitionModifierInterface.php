<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Grid\Definition\Modifier;

use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

interface GridDefinitionModifierInterface
{
	/**
	 * Used to modify Grid Definition.
	 *
	 * @param GridDefinitionInterface $gridDefinition
	 */
	public function modify(GridDefinitionInterface $gridDefinition);
}
