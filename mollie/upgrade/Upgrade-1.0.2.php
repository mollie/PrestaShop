<?php
/**
 * Upgrade script to upgrade Mollie to version 1.0.2
 * This function will not get called on new installation
 *
 * @param Mollie $module
 * @return bool
 */
function upgrade_module_1_0_2($module)
{
	return $module->installOpenState();
}