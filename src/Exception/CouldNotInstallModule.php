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

namespace Mollie\Exception;

use Mollie\Exception\Code\ExceptionCode;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotInstallModule extends MollieException
{
    public static function failedToInstallOrderState(string $orderStateName, \Exception $exception): self
    {
        return new self(
            sprintf('Failed to install order state (%s).', $orderStateName),
            ExceptionCode::INFRASTRUCTURE_FAILED_TO_INSTALL_ORDER_STATE,
            $exception
        );
    }
}
