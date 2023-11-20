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

namespace Mollie\Subscription\Install;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class AbstractUninstaller implements UninstallerInterface
{
    /** @var string[] */
    protected $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }
}
