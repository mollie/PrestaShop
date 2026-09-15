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

namespace Mollie\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hooks are registered when the module is installed and never again, so a shop that installed an
 * older version never picks up a hook added to the list afterwards. Sharing the rule between the
 * installer and the upgrade path keeps an upgraded shop hooked exactly like a fresh install.
 */
class HookRegistrationUtility
{
    /**
     * displayPaymentEU was replaced by paymentOptions in PrestaShop 1.7 and registering it there
     * would leave an upgraded shop hooked into something a clean install never registers.
     *
     * @param string[] $hooks every hook the module declares
     * @param string $psVersion PrestaShop version to resolve the rule against
     *
     * @return string[] the declared hooks this version installs, in their declared order
     */
    public static function installableHooks(array $hooks, $psVersion)
    {
        if (version_compare($psVersion, '1.7.0.0', '<')) {
            return array_values($hooks);
        }

        return array_values(array_filter($hooks, function ($hook) {
            return 'displayPaymentEU' !== $hook;
        }));
    }
}
