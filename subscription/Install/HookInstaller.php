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

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use Mollie;
use Mollie\Utility\PsVersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HookInstaller extends AbstractInstaller
{
    /** @var Mollie */
    private $module;

    public function __construct(
        Mollie $module
    ) {
        $this->module = $module;
    }

    public function install(): bool
    {
        $hooks = $this->getHooks();

        if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.7.0')) {
            $hooks[] = 'actionFrontControllerInitAfter';
        } else {
            $hooks[] = 'actionFrontControllerAfterInit';
        }

        /* @phpstan-ignore-next-line */
        $this->module->registerHook($hooks);

        return true;
    }

    /**
     * @return string[]
     */
    private function getHooks(): array
    {
        return [
            'actionFrontControllerSetMedia',
            'actionValidateOrder',
            'actionCartUpdateQuantityBefore',
            'actionObjectAddressAddAfter',
            'actionObjectAddressUpdateAfter',
            'actionObjectAddressDeleteAfter',
            'actionBeforeCartUpdateQty',
            'actionAjaxDieCartControllerDisplayAjaxUpdateBefore',
        ];
    }
}
