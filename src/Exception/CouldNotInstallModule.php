<?php

namespace Mollie\Exception;

class CouldNotInstallModule extends MollieException
{
    public static function failedToInstallOrderState(string $orderStateName, \Exception $exception): CouldNotInstallModule
    {
        return new self(
            sprintf('Failed to install order state (%s).', $orderStateName),
            ExceptionCode::INFRASTRUCTURE_FAILED_TO_INSTALL_ORDER_STATE,
            $exception
        );
    }
}
