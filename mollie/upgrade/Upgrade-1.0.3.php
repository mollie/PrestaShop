<?php
/**
 * @author    Bastiaan Peters <bastiaan@mollie.nl> September 03, 2014
 * @copyright Mollie B.V.
 *
 * Upgrade script to upgrade Mollie to version 1.0.3
 * This function will not get called on new installation
 *
 * @param Mollie $module
 * @return bool
 */
function upgrade_module_1_0_3($module)
{
	return $module->reinstallHooks() && $module->addCartIdChangePrimaryKey();
}