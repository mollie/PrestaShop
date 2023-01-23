<?php

namespace Mollie\Subscription\Install;

abstract class AbstractInstaller implements InstallerInterface
{
    /** @var string[] */
    protected $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }
}
