<?php

namespace Mollie\Exception;

use Mollie\Exception\Code\ExceptionCode;

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
