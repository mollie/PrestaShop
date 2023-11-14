<?php

namespace Mollie\Infrastructure\Exception;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\MollieException;

class CouldNotHandleLocking extends MollieException
{
    public static function lockExists(): self
    {
        return new self(
            'Lock exists',
            ExceptionCode::INFRASTRUCTURE_LOCK_EXISTS
        );
    }

    public static function lockOnAcquireIsMissing(): self
    {
        return new self(
            'Lock on acquire is missing',
            ExceptionCode::INFRASTRUCTURE_LOCK_ON_ACQUIRE_IS_MISSING
        );
    }

    public static function lockOnReleaseIsMissing(): self
    {
        return new self(
            'Lock on release is missing',
            ExceptionCode::INFRASTRUCTURE_LOCK_ON_RELEASE_IS_MISSING
        );
    }
}
