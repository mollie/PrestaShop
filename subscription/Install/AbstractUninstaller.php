<?php

namespace Mollie\Subscription\Install;

abstract class AbstractUninstaller implements UninstallerInterface
{
    /** @var string[] */
    protected $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }
}
